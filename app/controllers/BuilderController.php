<?php
declare(strict_types=1);
require_once ROOT_PATH.'/app/models/User.php';
require_once ROOT_PATH.'/app/models/Media.php';
require_once ROOT_PATH.'/app/core/builder/BuilderStore.php';
require_once ROOT_PATH.'/app/core/builder/SiteRenderer.php';
require_once ROOT_PATH.'/app/core/builder/EditableFields.php';
require_once ROOT_PATH.'/app/core/builder/DraftStructure.php';
final class BuilderController {
    public static function actor(): array {
        $user=(new User())->getCurrentUser();
        return $user?['id'=>(int)$user['id'],'role'=>$user['role'],'site_id'=>(int)($_SESSION['site_id']??0)]:[];
    }
    private function document(array $doc): array {
        $doc['presentation']??=LegacyViewData::capture(Database::getInstance()->getConnection());
        return $doc;
    }
    public function api(string $action): void {
        header('Content-Type: application/json');header('Cache-Control: private, no-store');
        try{
            $db=Database::getInstance()->getConnection();$store=new BuilderStore($db,TenantContext::id());$actor=self::actor();$store->authorize($actor);
            if(TenantContext::editorMode()!=='website_first')throw new RuntimeException('This site still uses the legacy editor.',409);
            if($action==='state'&&$_SERVER['REQUEST_METHOD']==='GET'){
                $state=$store->state();$draft=$this->document($store->draft($actor));$live=$this->document($store->published());
                echo json_encode(['revision'=>(int)$state['revision'],'document'=>$draft,'published'=>SiteDocument::encode($draft)===SiteDocument::encode($live)],JSON_THROW_ON_ERROR);return;
            }
            if($_SERVER['REQUEST_METHOD']!=='POST')throw new RuntimeException('Method not allowed.',405);
            if(!hash_equals(csrf_token(),(string)($_SERVER['HTTP_X_CSRF_TOKEN']??$_POST['csrf_token']??'')))throw new RuntimeException('Your session expired. Reload the page.',403);
            if($action==='upload'){
                $file=$_FILES['file']??[];$mime=isset($file['tmp_name'])?(new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']):'';
                if(!in_array($mime,['image/jpeg','image/png','image/webp','image/gif'],true))throw new InvalidArgumentException('Choose a JPG, PNG, WebP or GIF image.');
                $media=new Media();$id=$media->uploadFile($file,$actor['id']);$row=$media->find($id);
                echo json_encode(['path'=>$row['file_path'],'url'=>media_url($row['file_path'])],JSON_THROW_ON_ERROR);return;
            }
            if((int)($_SERVER['CONTENT_LENGTH']??0)>1048576)throw new InvalidArgumentException('Request is too large.');
            $input=json_decode(file_get_contents('php://input'),true,512,JSON_THROW_ON_ERROR);
            if(!is_array($input)||!is_int($input['revision']??null))throw new InvalidArgumentException('Missing draft revision.');
            $revision=$input['revision'];
            if($action==='save'){
                $doc=$this->document($store->draft($actor));
                if(!is_array($input['patches']??null)||count($input['patches'])>300)throw new InvalidArgumentException('Invalid changes.');
                foreach($input['patches'] as $id=>$value){
                    if(!is_string($value))throw new InvalidArgumentException('Editable values must be text.');
                    $spec=EditableFields::schema()[$id]??throw new InvalidArgumentException('Unknown editable field.');
                    if($spec['kind']==='image'&&$value!==EditableFields::value($doc,$spec)){
                        $q=$db->prepare("SELECT id FROM media_files WHERE file_path=? AND file_type='image'");$q->execute([$value]);
                        if($value!==''&&!$q->fetchColumn())throw new InvalidArgumentException('Choose an image owned by this website.');
                    }
                    EditableFields::apply($doc,$id,$value);
                }
                $next=$store->saveDraft($actor,$revision,$doc);
            }elseif($action==='structure'){
                $doc=$this->document($store->draft($actor));DraftStructure::apply($doc,$input);$next=$store->saveDraft($actor,$revision,$doc);
            }elseif($action==='publish')$next=$store->publish($actor,$revision);
            elseif($action==='discard')$next=$store->discard($actor,$revision);
            else throw new RuntimeException('Unknown editor action.',404);
            echo json_encode(['revision'=>$next,'published'=>$action==='publish'||$action==='discard'],JSON_THROW_ON_ERROR);
        }catch(Throwable $e){
            $status=$e instanceof InvalidArgumentException||$e instanceof JsonException?422:(in_array($e->getCode(),[403,404,405,409,429],true)?$e->getCode():500);
            http_response_code($status);echo json_encode(['error'=>$status===500?'The change could not be saved. Please retry.':$e->getMessage(),'code'=>$status===409?'conflict':'error']);
            if($status===500)error_log($e->getMessage());
        }
    }
    public function show(string $pageKey='home',bool $editing=false): void {
        $db=Database::getInstance()->getConnection();$store=new BuilderStore($db,TenantContext::id());$actor=self::actor();$owner=false;
        try{$store->authorize($actor);$owner=true;}catch(Throwable $e){}
        if($editing&&!$owner){http_response_code(403);echo 'Only the website owner can open the draft.';return;}
        header('Cache-Control: private, no-store');
        try{
            $document=$this->document($editing?$store->draft($actor):$store->published());
            if(!isset($document['pages'][$pageKey]) && !isset($document['compatibility']) && in_array($pageKey,['about','contact','portfolio','services'],true)) {
                $types=['about'=>['about','skills','experience'],'contact'=>['contact'],'portfolio'=>['projects'],'services'=>[]][$pageKey];
                $document['pages'][$pageKey]=['title'=>ucfirst($pageKey).' — '.$document['profile']['name'],'visible'=>true,'sections'=>array_values(array_filter($document['pages']['home']['sections'],fn($s)=>in_array($s['type'],$types,true)))];
            }
            echo (new SiteRenderer($db))->render($document,$pageKey,$editing,$owner,!isset($_GET['device_preview']));
        }catch(Throwable $e){http_response_code($e->getCode()===404?404:500);error_log($e->getMessage());echo $e->getCode()===404?'Page not found.':'Website could not be loaded.';}
    }
}
