<?php
declare(strict_types=1);
require_once __DIR__.'/SiteDocument.php';
final class TenantMigration {
    public function __construct(private PDO $db,private array $site) {}
    public function tables(): array {
        return $this->db->query("SHOW FULL TABLES WHERE Table_type='BASE TABLE'")->fetchAll(PDO::FETCH_COLUMN);
    }
    public function rows(string $table): array {
        if(!in_array($table,$this->tables(),true))return [];
        if(!preg_match('/^[a-zA-Z0-9_]+$/',$table))throw new RuntimeException('Invalid table name.');
        return $this->db->query('SELECT * FROM `'.$table.'`')->fetchAll(PDO::FETCH_ASSOC);
    }
    public function sourceHash(): string {
        $data=[]; $tables=$this->tables();sort($tables);
        foreach($tables as $table){
            if(str_starts_with($table,'builder_')||$table==='messages')continue;
            $rows=array_map(function($r)use($table){if($table==='skills')unset($r['status']);if($table==='admin_users')unset($r['last_login_at']);return json_encode($r,JSON_THROW_ON_ERROR);},$this->rows($table));sort($rows);$data[$table]=$rows;
        }
        return hash('sha256',json_encode($data,JSON_THROW_ON_ERROR));
    }
    public function backup(string $directory): string {
        if(!is_dir($directory)&&!mkdir($directory,0700,true))throw new RuntimeException('Cannot create backup directory.');
        $file=$directory.'/site-'.(int)$this->site['id'].'-'.gmdate('Ymd-His').'-'.bin2hex(random_bytes(4)).'.sql';
        $handle=fopen($file.'.partial','xb');if(!$handle)throw new RuntimeException('Cannot open backup.');
        $put=function(string $text)use($handle){if(fwrite($handle,$text)!==strlen($text))throw new RuntimeException('Backup write failed.');};
        try {
            $this->db->exec('SET TRANSACTION ISOLATION LEVEL REPEATABLE READ');$this->db->beginTransaction();
            $put("-- Tenant export. Restore into a NEW empty database; includes private account data.\nSET FOREIGN_KEY_CHECKS=0;\n");
            foreach($this->tables() as $table){
                if(!preg_match('/^[a-zA-Z0-9_]+$/',$table))throw new RuntimeException('Invalid table.');
                $create=$this->db->query('SHOW CREATE TABLE `'.$table.'`')->fetch(PDO::FETCH_NUM)[1];
                $put($create.";\n");
                foreach($this->rows($table) as $row){
                    $columns=implode(',',array_map(fn($key)=>'`'.$key.'`',array_keys($row)));
                    $values=implode(',',array_map(fn($v)=>$v===null?'NULL':"CONVERT(X'".bin2hex((string)$v)."' USING utf8mb4)",$row));
                    $put("INSERT INTO `".$table."` (".$columns.") VALUES (".$values.");\n");
                }
            }
            $put("SET FOREIGN_KEY_CHECKS=1;\n");$this->db->commit();
            if(!fflush($handle)||!fsync($handle))throw new RuntimeException('Backup could not be flushed.');
            fclose($handle);$handle=null;
            if(!rename($file.'.partial',$file))throw new RuntimeException('Backup could not be finalized.');
            if(file_put_contents($file.'.sha256',hash_file('sha256',$file))===false)throw new RuntimeException('Backup checksum could not be saved.');
            return $file;
        }catch(Throwable $e){if($this->db->inTransaction())$this->db->rollBack();if(is_resource($handle))fclose($handle);throw $e;}
    }
    public function schema(): void {
        $this->db->exec("CREATE TABLE IF NOT EXISTS builder_migrations(version INT PRIMARY KEY, applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, backup_path TEXT NOT NULL) ENGINE=InnoDB");
        $this->db->exec("CREATE TABLE IF NOT EXISTS builder_versions(id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, document_json LONGTEXT NOT NULL, created_by INT NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB");
        if(in_array('skills',$this->tables(),true)) {
            $columns=$this->db->query('SHOW COLUMNS FROM skills')->fetchAll(PDO::FETCH_COLUMN);
            if(!in_array('status',$columns,true))$this->db->exec("ALTER TABLE skills ADD COLUMN status ENUM('draft','published') NOT NULL DEFAULT 'draft'");
        }
        $this->db->exec("CREATE TABLE IF NOT EXISTS builder_state(id INT PRIMARY KEY,site_id INT NOT NULL,owner_id INT NULL,editor_mode ENUM('legacy','website_first') NOT NULL DEFAULT 'legacy',draft_json LONGTEXT NOT NULL,published_version_id BIGINT UNSIGNED NOT NULL,revision BIGINT NOT NULL DEFAULT 1,source_hash CHAR(64) NOT NULL,verified_hash CHAR(64) NULL,last_save_at DECIMAL(16,6) NULL,FOREIGN KEY(published_version_id) REFERENCES builder_versions(id)) ENGINE=InnoDB");
    }
    public function import(): array {
        $settings=$this->rows('settings_site')[0]??[];
        $hero=$this->rows('hero_section')[0]??[];
        $pages=[];foreach($this->rows('pages') as $p)$pages[$p['page_key']]=$p;
        $about=json_decode($pages['about']['sections']??'{}',true)?:[];
        $legacy=[];foreach($this->rows('site_settings') as $r)$legacy[$r['setting_key']]=$r['setting_value'];
        $doc=SiteDocument::fresh(['name'=>$settings['site_title']??$this->site['name'],'profession'=>$settings['site_tagline']??'','contact_email'=>$settings['contact_email']??'']);
        $doc['profile']['intro']=(string)($hero['hero_description']??$settings['site_description']??'');
        $doc['profile']['bio']=(string)($about['about_bio']??$about['about_content']??$pages['about']['content']??'');
        $doc['profile']['photo']=(string)($about['about_image_path']??'');
        $doc['profile']['phone']=(string)($settings['contact_phone']??'');
        $doc['profile']['location']=(string)($settings['contact_address']??'');
        $social=json_decode($settings['social_links']??'[]',true)?:[];
        $doc['profile']['social']=array_values(array_filter($social,fn($link)=>is_array($link)&&SiteDocument::validUrl((string)($link['url']??''))));
        if(!filter_var($doc['profile']['email'],FILTER_VALIDATE_EMAIL))$doc['profile']['email']='';
        $doc['theme']['color']=preg_match('/^#[a-fA-F0-9]{6}$/',$legacy['primary_color']??'')?$legacy['primary_color']:'#275dce';
        $home=&$doc['pages']['home'];
        $home['title']=(string)($pages['home']['title']??$doc['profile']['name']);
        $home['visible']=!isset($pages['home'])||!empty($pages['home']['is_active']);
        $heroSettings=&$home['sections'][0]['settings'];
        $heroSettings['heading']=(string)($hero['hero_title']??'');
        $heroSettings['button_label']=(string)($hero['hero_primary_cta_text']??'Get in touch');
        $link=(string)($hero['hero_primary_cta_url']??'');
        $heroSettings['button_url']=SiteDocument::validUrl($link)?$link:'';
        $keys=['hero'=>'home.section.hero-section','projects'=>'home.section.featured-work-section','skills'=>'home.section.skills-section','about'=>'home.section.about-section','contact'=>'home.section.cta-section'];
        foreach($home['sections'] as &$section){$key=$keys[$section['type']]??'';if($key){$section['visible']=($legacy['content.'.$key]??'1')!=='0';$section['order']=(int)($legacy['content.'.$key.'.order']??$section['order']);}}unset($section);
        foreach(['about'=>['about','skills','experience'],'contact'=>['contact'],'portfolio'=>['projects']] as $key=>$types){
            if(!isset($pages[$key]))continue;
            $doc['pages'][$key]=['title'=>(string)$pages[$key]['title'],'visible'=>!empty($pages[$key]['is_active']),'sections'=>array_values(array_filter($home['sections'],fn($s)=>in_array($s['type'],$types,true)))];
        }
        // Complete legacy records remain in their original tables and in the mandatory export.
        // The compatibility record prevents migration from pretending unsupported pages are converted.
        $doc['compatibility']=['legacy_pages'=>array_values(array_diff(array_keys($pages),array_keys($doc['pages']))),'requires_visual_review'=>true,'blocking_reasons'=>['Legacy navigation, footer, custom page labels and non-MVP sections require complete mapping before activation.']];
        return $doc;
    }
    public function owner(): ?int {
        foreach($this->rows('admin_users') as $user)if(($user['role']??'')==='superadmin'&&strcasecmp($user['email']??'', $this->site['owner_email']??'')===0)return (int)$user['id'];
        return null;
    }
    public function migrate(string $backupDirectory,bool $newSite=false,?array $fresh=null): array {
        $lock='builder-migrate:'.$this->site['database_name'];$q=$this->db->prepare('SELECT GET_LOCK(?,0)');$q->execute([$lock]);if((int)$q->fetchColumn()!==1)throw new RuntimeException('Migration already running.');
        try {
            if(in_array('builder_migrations',$this->tables(),true)&&$this->db->query('SELECT COUNT(*) FROM builder_migrations WHERE version=1')->fetchColumn())return ['status'=>'already_applied']+$this->verify();
            $backup=$this->backup($backupDirectory);
            $this->schema();
            $hash=$this->sourceHash();
            $document=$fresh??$this->import();$json=SiteDocument::encode($document);
            $owner=$this->owner();if($newSite&&!$owner)throw new RuntimeException('New site owner missing.');
            $this->db->beginTransaction();
            if(in_array('skills',$this->tables(),true))$this->db->exec("UPDATE skills SET status=IF(is_visible=1,'published','draft'),updated_at=updated_at");
            if(!$this->db->query('SELECT COUNT(*) FROM builder_state')->fetchColumn()){
                $q=$this->db->prepare('INSERT INTO builder_versions(document_json,created_by) VALUES (?,?)');$q->execute([$json,$owner]);$version=$this->db->lastInsertId();
                $q=$this->db->prepare('INSERT INTO builder_state(id,site_id,owner_id,editor_mode,draft_json,published_version_id,source_hash) VALUES (1,?,?,?,?,?,?)');
                $q->execute([$this->site['id'],$owner,$newSite?'website_first':'legacy',$json,$version,$hash]);
            }
            $q=$this->db->prepare('INSERT INTO builder_migrations(version,backup_path) VALUES (1,?)');$q->execute([$backup]);$this->db->commit();
            return ['status'=>'migrated','backup'=>$backup]+$this->verify();
        }catch(Throwable $e){if($this->db->inTransaction())$this->db->rollBack();throw $e;}
        finally{$q=$this->db->prepare('SELECT RELEASE_LOCK(?)');$q->execute([$lock]);}
    }
    public function verify(): array {
        if(!in_array('builder_state',$this->tables(),true))return ['schema_version'=>0,'editor_mode'=>'legacy'];
        $state=$this->db->query('SELECT * FROM builder_state WHERE id=1')->fetch(PDO::FETCH_ASSOC);
        if(!$state)return ['schema_version'=>0,'editor_mode'=>'legacy','warning'=>'Incomplete migration; rerun.'];
        $doc=json_decode($state['draft_json'],true,512,JSON_THROW_ON_ERROR);SiteDocument::validate($doc);
        $q=$this->db->prepare('SELECT document_json FROM builder_versions WHERE id=?');$q->execute([$state['published_version_id']]);$published=$q->fetchColumn();if(!$published)throw new RuntimeException('Published version missing.');
        SiteDocument::validate(json_decode($published,true,512,JSON_THROW_ON_ERROR));
        return ['schema_version'=>1,'editor_mode'=>$state['editor_mode'],'owner_resolved'=>$state['owner_id']!==null,'source_unchanged'=>hash_equals($state['source_hash'],$this->sourceHash()),'review_hash'=>hash('sha256',$state['draft_json']),'legacy_pages'=>$doc['compatibility']['legacy_pages']??[],'blocking_reasons'=>$doc['compatibility']['blocking_reasons']??[],'requires_visual_review'=>$state['editor_mode']==='legacy'];
    }
    public function refresh(string $backupDirectory): array {
        $state=$this->db->query('SELECT * FROM builder_state WHERE id=1')->fetch(PDO::FETCH_ASSOC);
        if(!$state||$state['editor_mode']!=='legacy')throw new RuntimeException('Only legacy migration candidates may be refreshed.');
        $this->backup($backupDirectory);
        $hash=$this->sourceHash();$json=SiteDocument::encode($this->import());$owner=$this->owner();
        $this->db->beginTransaction();
        try {
            $locked=$this->db->query('SELECT editor_mode FROM builder_state WHERE id=1 FOR UPDATE')->fetchColumn();
            if($locked!=='legacy')throw new RuntimeException('Website mode changed; refresh canceled.');
            if(in_array('skills',$this->tables(),true))$this->db->exec("UPDATE skills SET status=IF(is_visible=1,'published','draft'),updated_at=updated_at");
            $q=$this->db->prepare('INSERT INTO builder_versions(document_json,created_by) VALUES (?,?)');$q->execute([$json,$owner]);
            $version=$this->db->lastInsertId();
            $this->db->prepare('UPDATE builder_state SET draft_json=?,published_version_id=?,owner_id=?,source_hash=?,verified_hash=NULL,revision=revision+1 WHERE id=1')->execute([$json,$version,$owner,$hash]);
            $this->db->commit();return $this->verify();
        }catch(Throwable $e){if($this->db->inTransaction())$this->db->rollBack();throw $e;}
    }
    public function activate(string $reviewHash,string $backupDirectory): array {
        $report=$this->verify();
        if(empty($report['source_unchanged'])||empty($report['owner_resolved'])||!hash_equals($report['review_hash']??'', $reviewHash))throw new RuntimeException('Activation requires current source data, resolved owner and the exact reviewed document hash.');
        if(!empty($report['legacy_pages'])||!empty($report['blocking_reasons']))throw new RuntimeException('Activation blocked: remaining legacy pages need renderer coverage. Existing website remains unchanged.');
        $this->backup($backupDirectory);
        $this->db->prepare("UPDATE builder_state SET editor_mode='website_first',verified_hash=? WHERE id=1 AND editor_mode='legacy'")->execute([$reviewHash]);
        return $this->verify();
    }
}
