<?php
require_once __DIR__.'/../config/config.php';
require_once ROOT_PATH.'/app/core/builder/TenantMigration.php';
$registry=TenantContext::registry();$demo=$registry->query("SELECT * FROM sites WHERE slug='demo'")->fetch();
$connect=fn($name)=>new PDO('mysql:host='.DB_HOST.';port='.DB_PORT.';dbname='.$name.';charset=utf8mb4',DB_USER,DB_PASS,[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]);
$source=$connect($demo['database_name']);$export=(new TenantMigration($source,$demo))->backup(ROOT_PATH.'/.local/parity-backups');
$name='portfolio_parity_'.bin2hex(random_bytes(5));$slug='parity-'.bin2hex(random_bytes(4));
$registry->exec('CREATE DATABASE '.$name.' CHARACTER SET utf8mb4');$db=$connect($name);$db->exec(file_get_contents($export));
$q=$registry->prepare("INSERT INTO sites(slug,name,owner_email,database_name,status) VALUES (?,?,?,?,'active')");$q->execute([$slug,$demo['name'],$demo['owner_email'],$name]);$id=(int)$registry->lastInsertId();
$site=array_merge($demo,['id'=>$id,'slug'=>$slug,'database_name'=>$name]);$result=(new TenantMigration($db,$site))->migrate(ROOT_PATH.'/.local/parity-backups');
$state=$db->query('SELECT * FROM builder_state')->fetch();
if(in_array('--editor',$argv,true))$db->exec("UPDATE builder_state SET editor_mode='website_first' WHERE id=1");
file_put_contents(ROOT_PATH.(in_array('--editor',$argv,true)?'/.local/editor-fixture.json':'/.local/parity-fixture.json'),json_encode(['slug'=>$slug,'username'=>'admin','password'=>'password','schema'=>$result]));
echo "Prepared isolated parity fixture: ".$slug."\n";
