<?php
declare(strict_types=1);
require_once ROOT_PATH.'/app/models/User.php';
require_once ROOT_PATH.'/app/core/builder/BuilderStore.php';
require_once ROOT_PATH.'/app/core/builder/SiteRenderer.php';
final class BuilderController {
    public static function actor(): array {
        $user=(new User())->getCurrentUser();
        return $user?['id'=>(int)$user['id'],'role'=>$user['role'],'site_id'=>(int)($_SESSION['site_id']??0)]:[];
    }
    public function show(string $pageKey='home',bool $editing=false): void {
        $db=Database::getInstance()->getConnection();$store=new BuilderStore($db,TenantContext::id());$actor=self::actor();$owner=false;
        try{$store->authorize($actor);$owner=true;}catch(Throwable $e){}
        if($editing&&!$owner){http_response_code(403);echo 'Only the website owner can open the draft.';return;}
        header('Cache-Control: private, no-store');
        
        try{
            $document=$editing?$store->draft($actor):$store->published();
            if(!isset($document['pages'][$pageKey]) && !isset($document['compatibility']) && in_array($pageKey,['about','contact','portfolio','services'],true)) {
                $types=['about'=>['about','skills','experience'],'contact'=>['contact'],'portfolio'=>['projects'],'services'=>[]][$pageKey];
                $document['pages'][$pageKey]=['title'=>ucfirst($pageKey).' — '.$document['profile']['name'],'visible'=>true,'sections'=>array_values(array_filter($document['pages']['home']['sections'],fn($s)=>in_array($s['type'],$types,true)))];
            }
            echo (new SiteRenderer($db))->render($document,$pageKey,$editing,$owner);
        }catch(Throwable $e){http_response_code($e->getCode()===404?404:500);error_log($e->getMessage());echo $e->getCode()===404?'Page not found.':'Website could not be loaded.';}
    }
}
