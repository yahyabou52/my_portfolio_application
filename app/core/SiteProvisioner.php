<?php
require_once ROOT_PATH . '/scripts/update_schema.php';
final class SiteProvisioner {
    public static function create(string $name, string $slug, string $email, string $password): array {
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
            $db->exec(file_get_contents(ROOT_PATH . '/database/sample_data.sql'));
            ob_start();
            try { migratePortfolioSchema($db); } finally { ob_end_clean(); }
            // Start with an owner's identity, not the template author's profile or endorsements.
            $db->exec('DELETE FROM admin_users');
            $q = $db->prepare('INSERT INTO admin_users (name, username, email, password, role) VALUES (?, ?, ?, ?, ?)');
            $q->execute([$name, $email, $email, password_hash($password, PASSWORD_DEFAULT), 'superadmin']);
            $q = $db->prepare("UPDATE settings_site SET site_title=?, site_tagline='Independent professional', contact_email=?, contact_phone='', contact_address='', social_links='[]', footer_links='[]', logo_path='', favicon_path='', footer_text=?");
            $q->execute([$name, $email, '© ' . date('Y') . ' ' . $name]);
            $q = $db->prepare("UPDATE hero_section SET hero_intro_name_first=?, hero_intro_name_rest='', hero_intro_suffix='', hero_title='Your next great collaboration starts here', hero_subtitle='Thoughtful work. Lasting impact.', hero_description='Welcome to my portfolio. Explore my work and get in touch.', hero_background_image_path='assets/images/placeholder.svg', hero_background_image_alt='Portfolio cover'");
            $q->execute([$name]);
            foreach (['testimonials', 'timeline_items', 'messages', 'hero_stats'] as $table) { $db->exec('DELETE FROM ' . $table); }
            $q = $db->prepare("UPDATE pages SET title=CONCAT(UPPER(LEFT(page_key,1)), SUBSTRING(page_key,2), ' — ', ?), meta_description='', content='', sections='{}'");
            $q->execute([$name]);
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
