<?php
declare(strict_types=1);
require_once __DIR__.'/../config/config.php';
require_once ROOT_PATH.'/app/core/builder/TenantMigration.php';
$options=getopt('',['site:','all','dry-run','verify','refresh','activate','verified-hash:']);
if(!isset($options['site'])&&!isset($options['all'])){fwrite(STDERR,"Usage: php scripts/migrate-builder.php --site=demo|--all [--dry-run|--verify|--refresh|--activate --verified-hash=HASH]\n");exit(1);}
$registry=TenantContext::registry();
if(isset($options['site'])){$q=$registry->prepare('SELECT * FROM sites WHERE slug=?');$q->execute([$options['site']]);$sites=$q->fetchAll();}
else{$sites=$registry->query("SELECT * FROM sites WHERE status='active' ORDER BY id")->fetchAll();}
if(!$sites){fwrite(STDERR,"No matching sites.\n");exit(1);}
$failed=false;
foreach($sites as $site){
    try {
        if(!preg_match('/^[a-zA-Z0-9_]+$/',$site['database_name']))throw new RuntimeException('Invalid database identifier.');
        $db=new PDO('mysql:host='.DB_HOST.';port='.DB_PORT.';dbname='.$site['database_name'].';charset=utf8mb4',DB_USER,DB_PASS,[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]);
        $migration=new TenantMigration($db,$site);
        if(isset($options['dry-run']))$result=['action'=>'dry-run','will_export_before_writes'=>true]+$migration->verify();
        elseif(isset($options['verify']))$result=$migration->verify();
        elseif(isset($options['refresh']))$result=$migration->refresh(ROOT_PATH.'/.local/builder-backups');
        elseif(isset($options['activate']))$result=$migration->activate((string)($options['verified-hash']??''),ROOT_PATH.'/.local/builder-backups');
        else $result=$migration->migrate(ROOT_PATH.'/.local/builder-backups');
        $entry=['site_id'=>(int)$site['id'],'slug'=>$site['slug'],'time'=>gmdate('c'),'result'=>$result];
    }catch(Throwable $e){$failed=true;$entry=['site_id'=>(int)$site['id'],'slug'=>$site['slug'],'time'=>gmdate('c'),'error'=>$e->getMessage()];}
    $line=json_encode($entry,JSON_THROW_ON_ERROR|JSON_UNESCAPED_SLASHES)."\n";echo $line;
    if(!isset($options['dry-run'])){if(!is_dir(ROOT_PATH.'/.local'))mkdir(ROOT_PATH.'/.local',0700,true);file_put_contents(ROOT_PATH.'/.local/builder-migrations.jsonl',$line,FILE_APPEND|LOCK_EX);}
}
exit($failed?1:0);
