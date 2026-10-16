<?php
declare(strict_types=1);
ob_start();
require_once __DIR__.'/../config/config.php';
require_once ROOT_PATH.'/app/core/builder/TenantMigration.php';
require_once ROOT_PATH.'/app/core/builder/BuilderStore.php';
require_once ROOT_PATH.'/app/core/builder/SiteRenderer.php';
require_once ROOT_PATH.'/app/core/SiteProvisioner.php';
function check(bool $ok,string $message): void {if(!$ok)throw new RuntimeException('FAIL: '.$message);echo "PASS: ".$message."\n";}
function rejects(callable $fn,int $code,string $message): void {try{$fn();}catch(Throwable $e){check($e->getCode()===$code,$message.' ('.$e->getMessage().')');return;}throw new RuntimeException('FAIL: '.$message);}
function connectFixture(string $name): PDO {
    return new PDO('mysql:host='.DB_HOST.';port='.DB_PORT.';dbname='.$name.';charset=utf8mb4',DB_USER,DB_PASS,[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]);
}
$registry=TenantContext::registry();$demo=$registry->query("SELECT * FROM sites WHERE slug='demo'")->fetch();
if(!$demo)throw new RuntimeException('Local demo fixture is required.');
$source=connectFixture($demo['database_name']);$exporter=new TenantMigration($source,$demo);
$hash=$exporter->sourceHash();$backup=$exporter->backup(ROOT_PATH.'/.local/builder-test-backups');
check(hash_file('sha256',$backup)===file_get_contents($backup.'.sha256'),'Backup checksum');
$server=new PDO('mysql:host='.DB_HOST.';port='.DB_PORT,DB_USER,DB_PASS,[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
$fixture='portfolio_builder_test_'.bin2hex(random_bytes(5));
$server->exec('CREATE DATABASE '.$fixture.' CHARACTER SET utf8mb4');
$db=connectFixture($fixture);$db->exec(file_get_contents($backup));
$site=$demo;$site['database_name']=$fixture;$site['id']=900001;
$migration=new TenantMigration($db,$site);
check($hash===$migration->sourceHash(),'Restored real-shaped export preserves every source record');
$first=$migration->migrate(ROOT_PATH.'/.local/builder-test-backups');
check($first['editor_mode']==='legacy','Existing site stays legacy');
check($first['source_unchanged'],'Migration preserves legacy source data');
$state=$db->query('SELECT * FROM builder_state')->fetch();$versions=$db->query('SELECT COUNT(*) FROM builder_versions')->fetchColumn();
$second=$migration->migrate(ROOT_PATH.'/.local/builder-test-backups');
check($second['status']==='already_applied'&&$versions===$db->query('SELECT COUNT(*) FROM builder_versions')->fetchColumn(),'Migration is idempotent');
check($state===$db->query('SELECT * FROM builder_state')->fetch(),'Rerun preserves draft, revision and mode');
$store=new BuilderStore($db,900001);
$actor=['id'=>(int)$state['owner_id'],'site_id'=>900001,'role'=>'superadmin'];
check($actor['id']>0,'Owner resolved from existing account');
rejects(fn()=>$store->discard($actor,1),409,'Legacy candidate cannot bypass its authoritative editor');
$db->exec("UPDATE builder_state SET editor_mode='website_first' WHERE id=1"); // Test fixture only; production activation remains guarded.
$published=$store->published();$draft=$store->draft($actor);$draft['profile']['intro']='PRIVATE DRAFT ONLY';
$revision=$store->saveDraft($actor,1,$draft);
check($store->published()===$published,'Draft save leaves public snapshot unchanged');
rejects(fn()=>$store->saveDraft($actor,1,$draft),409,'Stale revision rejected');
rejects(fn()=>$store->saveDraft($actor,$revision,$draft),429,'Autosave rate limited');
$other=$actor;$other['site_id']=900002;
rejects(fn()=>$store->draft($other),403,'Site A identity cannot read site B draft');
rejects(fn()=>$store->publish($other,$revision),403,'Site A identity cannot publish site B');
$editor=$actor;$editor['role']='editor';rejects(fn()=>$store->discard($editor,$revision),403,'Non-owner role cannot write');
$count=$db->query('SELECT COUNT(*) FROM builder_versions')->fetchColumn();
$db->exec("CREATE TRIGGER fail_publish BEFORE UPDATE ON builder_state FOR EACH ROW BEGIN IF NEW.published_version_id <> OLD.published_version_id THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Injected failure after version insertion'; END IF; END");
try{$store->publish($actor,$revision);throw new RuntimeException('Failure injection not reached');}catch(PDOException $expected){}
check($store->published()===$published&&(int)$store->state()['revision']===$revision&&$count===$db->query('SELECT COUNT(*) FROM builder_versions')->fetchColumn(),'Failed publish rolls back both snapshot insertion and pointer');
$db->exec('DROP TRIGGER fail_publish');
$revision=$store->discard($actor,$revision);check($store->draft($actor)===$published,'Discard restores last published version');
$db->exec('UPDATE builder_state SET last_save_at=NULL');
$revision=$store->saveDraft($actor,$revision,$draft);$revision=$store->publish($actor,$revision);
check($store->published()['profile']['intro']==='PRIVATE DRAFT ONLY','Publish switches public snapshot');
$bad=$draft;$bad['pages']['home']['sections'][0]['settings']['button_url']='javascript:alert(1)';
rejects(fn()=>$store->saveDraft($actor,$revision,$bad),0,'Unsafe URL scheme rejected');
foreach(['javascript:alert(1)','data:text/html,x','//evil.example','https://example.com\\evil'] as $url)check(!SiteDocument::validUrl($url),'Reject unsafe link '.$url);
foreach(['https://example.com','http://example.com','mailto:hello@example.com','tel:+1234567','/contact','#contact'] as $url)check(SiteDocument::validUrl($url),'Accept safe link '.$url);
$render=new SiteRenderer($db);
$html=$render->render($published,'home',false,false);
check(!str_contains($html,'PRIVATE DRAFT ONLY')&&!str_contains($html,'owner-edit')&&!str_contains($html,'builder-bar'),'Visitor render contains no draft or editor UI');
$published['profile']['name']='<script>alert(1)</script>';
check(!str_contains($render->render($published,'home'),'<script>alert(1)</script>'),'Profile output escaped');
// Sparse tenant plus interrupted DDL: resume without changing or requiring absent legacy content.
$partial='portfolio_builder_partial_'.bin2hex(random_bytes(5));$server->exec('CREATE DATABASE '.$partial.' CHARACTER SET utf8mb4');$sparse=connectFixture($partial);
$sparse->exec('CREATE TABLE settings_site(id INT PRIMARY KEY,site_title VARCHAR(150)); INSERT INTO settings_site VALUES (1,"Sparse portfolio")');
$partialSite=['id'=>900002,'name'=>'Sparse portfolio','owner_email'=>'missing@example.com','database_name'=>$partial];
$partialMigration=new TenantMigration($sparse,$partialSite);$partialMigration->schema();
$report=$partialMigration->migrate(ROOT_PATH.'/.local/builder-test-backups');
check($report['schema_version']===1&&!$report['owner_resolved'],'Partial migration resumes with missing profile and collections');
check($report['editor_mode']==='legacy','Unresolved owner cannot activate');
rejects(fn()=>$partialMigration->activate($report['review_hash'],ROOT_PATH.'/.local/builder-test-backups'),0,'Activation fails closed for unresolved ownership');
// Source drift never silently activates an outdated candidate; refresh archives the previous candidate.
$sparse->exec("UPDATE settings_site SET site_title='Changed sparse portfolio' WHERE id=1");
check(!$partialMigration->verify()['source_unchanged'],'Source drift detected');
$refresh=$partialMigration->refresh(ROOT_PATH.'/.local/builder-test-backups');check($refresh['source_unchanged'],'Legacy candidate refresh records current source');
check($sparse->query('SELECT COUNT(*) FROM builder_versions')->fetchColumn()==2,'Refresh retains previous candidate version');
$refused=$partialMigration->verify();check($refused['blocking_reasons']!==[],'Unmapped legacy content blocks activation');
// Export failure aborts before adding migration tables.
$failedDb='portfolio_builder_backup_failure_'.bin2hex(random_bytes(5));$server->exec('CREATE DATABASE '.$failedDb.' CHARACTER SET utf8mb4');$failedConnection=connectFixture($failedDb);
$blockedDirectory=ROOT_PATH.'/.local/not-a-backup-directory';file_put_contents($blockedDirectory,'test fixture');
$failedMigration=new TenantMigration($failedConnection,['id'=>900003,'name'=>'Backup failure','owner_email'=>'','database_name'=>$failedDb]);
try{@$failedMigration->migrate($blockedDirectory);throw new RuntimeException('Expected export failure');}catch(RuntimeException $error){check(str_contains($error->getMessage(),'backup directory'),'Backup failure reported');}
check($failedMigration->tables()===[],'Backup failure leaves tenant schema untouched');
// Create two real HTTP-test tenants using the production signup provisioner.
$credentials=[];
foreach(['a','b'] as $letter){
    $slug='builder-'.$letter.'-'.bin2hex(random_bytes(4));$password=bin2hex(random_bytes(15));$email=$slug.'@example.com';
    $created=SiteProvisioner::create('Builder '.$letter,$slug,$email,$password);$q=$registry->prepare('SELECT * FROM sites WHERE id=?');$q->execute([$created['id']]);$record=$q->fetch();$tenant=connectFixture($record['database_name']);
    $tenantState=$tenant->query('SELECT * FROM builder_state')->fetch();
    check($tenantState['editor_mode']==='website_first','New website starts website_first');
    foreach(['projects','skills','timeline_items','testimonials','services'] as $table)check((int)$tenant->query('SELECT COUNT(*) FROM '.$table)->fetchColumn()===0,'New website has no sample '.$table);
    $doc=json_decode($tenantState['draft_json'],true);check($doc['profile']['email']==='','Account email is not silently published as contact information');
    $empty=(new SiteRenderer($tenant))->render($doc,'home',false,false);$editing=(new SiteRenderer($tenant))->render($doc,'home',true,true);
    check(!str_contains($empty,'featured-work-section')&&!str_contains($editing,'featured-work-section'),'Empty collection markup is omitted without inventing section layouts');
    if($letter==='a'){
        $owner=['id'=>(int)$tenantState['owner_id'],'site_id'=>(int)$created['id'],'role'=>'superadmin'];$doc['profile']['intro']='DRAFT_SECRET_HTTP';
        (new BuilderStore($tenant,(int)$created['id']))->saveDraft($owner,1,$doc);
    }
    $tenant->prepare('INSERT INTO projects(title,slug,status) VALUES (?,?,?)')->execute([$letter==='a'?'PRIVATE PROJECT':'Public project',$letter==='a'?'private-project':'public-project',$letter==='a'?'draft':'published']);
    if($letter==='b'){
        $owner=['id'=>(int)$tenantState['owner_id'],'site_id'=>(int)$created['id'],'role'=>'superadmin'];$doc['profile']['name']='Published canonical profile';
        $storeB=new BuilderStore($tenant,(int)$created['id']);$next=$storeB->saveDraft($owner,1,$doc);$storeB->publish($owner,$next);
    }
    $credentials[]=['slug'=>$slug,'email'=>$email,'password'=>$password,'site_id'=>(int)$created['id']];
}
// Preserve the original legacy CRUD suite using restored legacy fixtures, not the changed signup flow.
$legacyCredentials=[];
for($i=0;$i<2;$i++) {
    $slug='legacy-qa-'.bin2hex(random_bytes(4));$database='portfolio_legacy_test_'.bin2hex(random_bytes(5));$email=$slug.'@example.com';$password=bin2hex(random_bytes(15));
    $server->exec('CREATE DATABASE '.$database.' CHARACTER SET utf8mb4');$legacy=connectFixture($database);$legacy->exec(file_get_contents($backup));
    $legacy->exec('DELETE FROM admin_users');
    $legacy->prepare("INSERT INTO admin_users(name,username,email,password,role) VALUES (?,?,?,?,'superadmin')")->execute(['Test Studio '.$i,$email,$email,password_hash($password,PASSWORD_DEFAULT)]);
    $legacy->prepare('UPDATE settings_site SET site_title=? WHERE id=1')->execute(['Test Studio '.$i]);
    $registry->prepare("INSERT INTO sites(slug,name,owner_email,database_name,status) VALUES (?,?,?,?,'active')")->execute([$slug,'Test Studio '.$i,$email,$database]);
    $legacyCredentials[]=['slug'=>$slug,'email'=>$email,'password'=>$password];
}
file_put_contents(ROOT_PATH.'/.local/legacy-test-sites.json',json_encode($legacyCredentials,JSON_THROW_ON_ERROR));
file_put_contents(ROOT_PATH.'/.local/builder-test-sites.json',json_encode($credentials,JSON_THROW_ON_ERROR));
check($hash===$exporter->sourceHash(),'Original demo is unchanged');
echo "Phase 1 data tests passed. Fixture databases retained: ".$fixture.", ".$partial."\n";
