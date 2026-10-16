<?php
require_once ROOT_PATH . '/scripts/update_schema.php';
final class SiteProvisioner {
    public static function create(string $name, string $slug, string $email, string $password,array $answers=[],?array $photo=null): array {
        $registry = TenantContext::registry();
        $database = 'portfolio_site_' . bin2hex(random_bytes(8));
        $insert = $registry->prepare('INSERT INTO sites (slug, name, owner_email, database_name) VALUES (?, ?, ?, ?)');
        $insert->execute([$slug, $name, $email, $database]);
        $id = (int)$registry->lastInsertId();
        try {
            $registry->exec('CREATE DATABASE ' . $database . ' CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
            $db = new PDO('mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . $database . ';charset=utf8mb4', DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
            // Never execute the seed's USE or CREATE DATABASE against the shared registry/demo.
            $schema = file_get_contents(ROOT_PATH . '/database/schema.sql');
            $schema = preg_replace('/^(CREATE DATABASE[^;]*;|USE [^;]*;)\s*/m', '', $schema);
            $db->exec($schema);
            // Fresh sites intentionally have no sample collections.
            ob_start();
            try { migratePortfolioSchema($db); } finally { ob_end_clean(); }
            // Start with an owner's identity, not the template author's profile or endorsements.
            $db->exec('DELETE FROM admin_users');
            $q = $db->prepare('INSERT INTO admin_users (name, username, email, password, role) VALUES (?, ?, ?, ?, ?)');
            $q->execute([$name, $email, $email, password_hash($password, PASSWORD_DEFAULT), 'superadmin']);
            $q = $db->prepare("UPDATE settings_site SET site_title=?, site_tagline='', contact_email='', contact_phone='', contact_address='', social_links='[]', footer_links='[]', logo_path='', favicon_path='', footer_text=?");
            $q->execute([$name, '© ' . date('Y') . ' ' . $name]);
            $q = $db->prepare("UPDATE hero_section SET hero_intro_name_first=?, hero_intro_name_rest='', hero_intro_suffix='', hero_title='Your next great collaboration starts here', hero_subtitle='Thoughtful work. Lasting impact.', hero_description='Welcome to my portfolio. Explore my work and get in touch.', hero_background_image_path='assets/images/placeholder.svg', hero_background_image_alt='Portfolio cover'");
            $q->execute([$name]);
            foreach (['testimonials', 'timeline_items', 'messages', 'hero_stats'] as $table) { $db->exec('DELETE FROM ' . $table); }
            $q = $db->prepare("UPDATE pages SET title=CONCAT(UPPER(LEFT(page_key,1)), SUBSTRING(page_key,2), ' — ', ?), meta_description='', content='', sections='{}'");
            $q->execute([$name]);
            require_once ROOT_PATH.'/app/core/builder/TenantMigration.php';
            $siteRecord=['id'=>$id,'slug'=>$slug,'name'=>$name,'owner_email'=>$email,'database_name'=>$database];
            // Only new websites change insertion defaults; existing customer rows are untouched.
            foreach(['projects','services','timeline_items','testimonials'] as $table)$db->exec("ALTER TABLE `".$table."` ALTER COLUMN status SET DEFAULT 'draft'");
            $document=SiteDocument::fresh(array_merge($answers,['name'=>$name]));
            if($photo&&($photo['error']??UPLOAD_ERR_NO_FILE)!==UPLOAD_ERR_NO_FILE){
                require_once ROOT_PATH.'/app/models/Media.php';
                $mime=(new finfo(FILEINFO_MIME_TYPE))->file($photo['tmp_name']);
                if(!in_array($mime,['image/jpeg','image/png','image/webp','image/gif'],true))throw new InvalidArgumentException('Choose a JPG, PNG, WebP or GIF photo.');
                $document['profile']['photo']=TenantContext::using($siteRecord,fn()=>Database::using($db,function()use($photo){$media=new Media();$owner=Database::getInstance()->getConnection()->query("SELECT id FROM admin_users WHERE role='superadmin' LIMIT 1")->fetchColumn();$id=$media->uploadFile($photo,$owner);return $media->find($id)['file_path'];}));
            }
            $document['presentation']=LegacyViewData::capture($db);
            $document['presentation']['global']['navigationMenu']=[['title'=>'Home','url'=>'/','children'=>[]]];
            unset($document['presentation']['hero']['hero_title'],$document['presentation']['hero']['hero_background_image_path'],$document['presentation']['hero']['hero_background_image_url']);
            $document['presentation']['hero']['hero_subtitle']='';
            $document['presentation']['hero']['hero_primary_cta_text']='View my work';
            $document['presentation']['hero']['hero_primary_cta_url']='/portfolio';
            $document['presentation']['hero']['hero_secondary_cta_text']='Get in touch';
            $document['presentation']['hero']['hero_secondary_cta_url']='/contact';
            $document['presentation']['about']['headline']='About me';
            $document['presentation']['about']['greeting']='Hello';
            $document['presentation']['about']['philosophy']='';
            (new TenantMigration($db,$siteRecord))->migrate(ROOT_PATH.'/.local/builder-backups',true,$document);
            $registry->prepare("UPDATE sites SET status='active' WHERE id=?")->execute([$id]);
            return ['id'=>$id, 'slug'=>$slug];
        } catch (Throwable $e) {
            // Only clean up the random database allocated by this call.
            $registry->exec('DROP DATABASE IF EXISTS ' . $database);
            $registry->prepare('DELETE FROM sites WHERE id=? AND status=?')->execute([$id, 'provisioning']);
            throw $e;
        }
    }
}
