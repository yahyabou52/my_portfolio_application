<?php
require_once ROOT_PATH . '/app/core/BaseController.php';
require_once ROOT_PATH . '/app/models/User.php';
require_once ROOT_PATH . '/app/models/Media.php';
require_once ROOT_PATH . '/app/models/Setting.php';
final class WebsiteController extends BaseController {
    private const IDENTITY = ['site_title'=>'Website name','site_tagline'=>'Tagline','site_description'=>'Website description','contact_email'=>'Contact email','contact_phone'=>'Phone','contact_address'=>'Location'];
    private function pages(): array { return require ROOT_PATH . '/config/editor-pages.php'; }
    public function edit(): void {
        (new User())->requireAuth();
        $group=(string)($_GET['group'] ?? (str_contains($_SERVER['REQUEST_URI']??'', '/admin/settings')?'identity':'home'));
        $pages=$this->pages();
        if (!isset($pages[$group])) { http_response_code(404); echo 'Editor page not found.'; return; }
        $fields=array_filter(WebsiteContent::manifest(),fn($f)=>$f['group']===$group && empty($f['fallbackOnly']));
        $draft=$_SESSION['website_draft'][$group]??[];
        unset($_SESSION['website_draft'][$group]);
        $previewPath=$pages[$group]['path'];
        if($group==='project') { $project=(new Project())->getPublished(1); if($project) { $previewPath='portfolio/'.$project[0]['slug']; } }
        $this->render('admin/website','admin',[
            'title'=>$pages[$group]['title'].' — Website editor','page'=>'admin-website','group'=>$group,
            'editor_pages'=>$pages,'fields'=>$fields,'identity_fields'=>self::IDENTITY,
            'identity'=>(new SiteSetting())->getSettings(),'images'=>(new Media())->getImages(),
            'page_record'=>(new Page())->getByKey($group),'draft'=>$draft,
            'preview_url'=>url($previewPath).'?preview=1',
        ]);
    }
    public function save(): void {
        (new User())->requireAuth(); verify_csrf();
        $group=(string)($_POST['group']??'home');
        if(!isset($this->pages()[$group])) { http_response_code(422); echo 'Choose a valid editor page.'; return; }
        $db=Database::getInstance()->getConnection();
        try {
            $db->beginTransaction();
            $siteModel=new SiteSetting(); $current=$siteModel->getSettings();
            if($group==='identity') {
                $data=[];
                foreach(self::IDENTITY as $key=>$label) {
                    $value=trim((string)($_POST[$key]??$current[$key]??''));
                    $max=$key==='site_description'?3000:($key==='contact_phone'?80:150);
                    if(mb_strlen($value)>$max) { throw new InvalidArgumentException($label.' is too long.'); }
                    $data[$key]=$value;
                }
                if($data['site_title']==='') { throw new InvalidArgumentException('Enter a website name.'); }
                if($data['contact_email']!==''&&!filter_var($data['contact_email'],FILTER_VALIDATE_EMAIL)) { throw new InvalidArgumentException('Enter a valid contact email.'); }
                $media=new Media();
                foreach(['logo_path'=>'logo_id','favicon_path'=>'favicon_id'] as $column=>$input) {
                    $selected=(string)($_POST[$input]??'keep');
                    if($selected==='') { $data[$column]=''; }
                    elseif($selected!=='keep') { $image=$media->find((int)$selected); if(!$image||$image['file_type']!=='image') { throw new InvalidArgumentException('Choose an image from your media library.'); } $data[$column]=$image['file_path']; }
                }
                if(isset($_POST['social'])) {
                    $social=[];
                    foreach(['linkedin','github','dribbble','behance','instagram','facebook'] as $platform) {
                        $value=trim((string)($_POST['social'][$platform]??''));
                        if($value!=='') { if(!filter_var($value,FILTER_VALIDATE_URL)||!preg_match('#^https?://#',$value)) { throw new InvalidArgumentException('Social links must start with https:// or http://.'); } $social[]=['platform'=>$platform,'url'=>$value]; }
                    }
                    $data['social_links']=json_encode($social,JSON_THROW_ON_ERROR);
                }
                $siteModel->updateSettings($data);
            } else {
                $values=[];
                foreach(WebsiteContent::manifest() as $key=>$field) {
                    if($field['group']!==$group||!empty($field['fallbackOnly'])) { continue; }
                    $previous=WebsiteContent::get($key,$field['default']);
                    if($field['type']==='visibility') { $value=isset($_POST['_layout'])||isset($_POST['fields']) ? (isset($_POST['fields'][$key])?'1':'0') : $previous; }
                    else { $value=(string)($_POST['fields'][$key]??$previous); }
                    if($field['type']==='order'&&(!ctype_digit($value)||(int)$value>1000)) { throw new InvalidArgumentException('Section positions must be between 0 and 1000.'); }
                    if(mb_strlen($value)>5000) { throw new InvalidArgumentException('Keep each text field below 5,000 characters.'); }
                    $values[$key]=$value;
                }
                if($group==='navigation'&&isset($_POST['nav_cta_text'])) {
                    $label=trim((string)$_POST['nav_cta_text']); $link=trim((string)($_POST['nav_cta_url']??''));
                    if(mb_strlen($label)>120||strlen($link)>255||($link!==''&&!$this->validLink($link))) { throw new InvalidArgumentException('Enter a short button label and a relative or http(s) link.'); }
                    $siteModel->updateSettings(['nav_cta_text'=>$label,'nav_cta_url'=>$link]);
                }
                if($group==='footer') {
                    $data=[];
                    if(array_key_exists('footer_text',$_POST)) { $text=(string)$_POST['footer_text']; if(mb_strlen($text)>3000)throw new InvalidArgumentException('Footer text is too long.'); $data['footer_text']=$text; }
                    if(array_key_exists('footer_links',$_POST)) {
                        $links=[];
                        foreach(array_slice((array)$_POST['footer_links'],0,8) as $link) {
                            if(!is_array($link))continue;
                            $title=trim((string)($link['title']??''));$url=trim((string)($link['url']??''));
                            if($title===''&&$url==='')continue;
                            if($title===''||mb_strlen($title)>100||strlen($url)>500||!$this->validLink($url))throw new InvalidArgumentException('Footer links need a label and a relative or http(s) URL.');
                            $links[]=['title'=>$title,'url'=>$url,'target'=>'_self'];
                        }
                        $data['footer_links']=json_encode($links,JSON_THROW_ON_ERROR);
                    }
                    if($data)$siteModel->updateSettings($data);
                    if(isset($_POST['_footer'])) {
                        $extra=(string)($_POST['footer_additional_text']??'');
                        if(mb_strlen($extra)>3000)throw new InvalidArgumentException('Additional footer text is too long.');
                        $settings=new Setting();$settings->updateByKey('footer_additional_text',$extra);$settings->updateByKey('footer_show_social',isset($_POST['footer_show_social'])?'1':'0');
                    }
                }
                WebsiteContent::save($values);
                if(isset($_POST['page_meta'])&&in_array($group,['home','about','services','portfolio','contact'],true)) {
                    $meta=$_POST['page_meta'];$title=trim((string)($meta['title']??''));$description=trim((string)($meta['meta_description']??''));
                    if($title===''||mb_strlen($title)>200||mb_strlen($description)>300)throw new InvalidArgumentException('Enter a browser title under 200 characters and a description under 300.');
                    (new Page())->updateByKey($group,['title'=>$title,'meta_description'=>$description,'is_active'=>isset($meta['is_active'])?1:0]);
                }
            }
            $db->commit(); unset($_SESSION['website_draft'][$group]);
            $this->setFlash('success','Changes saved. Your website is up to date.');
        } catch(Throwable $e) {
            if($db->inTransaction())$db->rollBack();
            error_log($e->getMessage());
            $draft=$_POST;unset($draft['csrf_token']);$_SESSION['website_draft'][$group]=$draft;
            $this->setFlash('error',$e instanceof InvalidArgumentException?$e->getMessage():'Changes could not be saved. Your edits are still here; please try again.');
        }
        $this->redirect('admin/website?group='.urlencode($group));
    }
    private function validLink(string $url): bool { return (bool)preg_match('#^(https?://|/(?!/)|\#)#',$url); }
}
