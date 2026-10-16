<?php
declare(strict_types=1);
require_once __DIR__.'/SiteDocument.php';
require_once __DIR__.'/LegacyViewData.php';
require_once __DIR__.'/EditableFields.php';
final class SiteRenderer {
    public function __construct(private PDO $db) {}
    protected function getFlash(?string $key=null) {return $key?($_SESSION['flash'][$key]??[]):($_SESSION['flash']??[]);}
    protected function hasFlash(): bool {return !empty($_SESSION['flash']);}
    public function collections(string $type): array {
        $table=SectionRegistry::get($type)['collection']??null;if(!$table)return [];
        try { return $this->db->query("SELECT * FROM `".$table."` WHERE status='published' ORDER BY sort_order,id")->fetchAll(PDO::FETCH_ASSOC); }
        catch(PDOException $e){if($e->getCode()==='42S02')return [];throw $e;}
    }
    public static function sections(string $html): array {
        preg_match_all('/<section\b[^>]*>|<\/section>/i',$html,$tags,PREG_OFFSET_CAPTURE);
        $depth=0;$start=0;$key='';$result=[];
        foreach($tags[0] as [$tag,$offset]){
            if(str_starts_with(strtolower($tag),'</')){if(--$depth===0&&$key!=='')$result[$key]=substr($html,$start,$offset+strlen($tag)-$start);}
            else{if($depth===0){$start=$offset;preg_match('/data-section-key="([^"]+)"/',$tag,$m);$key=$m[1]??'';}$depth++;}
        }
        return $result;
    }
    private function view(string $view,array $data): string {
        extract($data,EXTR_SKIP);ob_start();require ROOT_PATH.'/app/views/'.$view.'.php';return ob_get_clean();
    }
    public function render(array $document,string $pageKey,bool $editing=false,bool $owner=false): string {
        SiteDocument::validate($document);
        $page=$document['pages'][$pageKey]??null;
        if(!$page||(!$page['visible']&&!$editing))throw new RuntimeException('Page not found.',404);
        $presentation=$document['presentation']??LegacyViewData::capture($this->db);
        $document['presentation']=$presentation;
        return WebsiteContent::using($presentation['values'],function()use($document,$pageKey,$page,$editing,$owner){
            $data=LegacyViewData::data($this->db,$document,$pageKey);
            $views=['home'=>'home/index','about'=>'home/about','portfolio'=>'portfolio/index','contact'=>'contact/index','services'=>'home/services'];
            // Existing migrated pages preserve every original section and their local scripts.
            if(!empty($document['compatibility'])){
                $content=$this->view($views[$pageKey]??'home/index',$data);
                $content=WebsiteContent::orderSections($content);
            }else{
                $sources=[];
                foreach(['home','about','contact'] as $source){$sourceData=LegacyViewData::data($this->db,$document,$source);$sources+=self::sections($this->view($views[$source],$sourceData));}
                $sections=$page['sections'];usort($sections,fn($a,$b)=>$a['order']<=>$b['order']);$content='';
                $map=['hero'=>'home.section.hero-section','about'=>'about.section.about-hero','projects'=>'home.section.featured-work-section','skills'=>'home.section.skills-section','experience'=>'about.section.experience-section','contact'=>'contact.section.contact-content'];
                foreach($sections as $section){
                    if(!$section['visible'])continue;
                    if(isset(SectionRegistry::get($section['type'])['collection'])&&!$this->collections($section['type']))continue;
                    if($section['type']==='about'&&trim($document['profile']['bio'])==='')continue;
                    if($section['type']==='contact'&&$document['profile']['email']===''&&$document['profile']['phone']==='')continue;
                    $html=$sources[$map[$section['type']]]??'';
                    $html=preg_replace('/<section\b/','<section data-section-id="'.htmlspecialchars($section['id'],ENT_QUOTES).'"',$html,1);
                    $content.=$html;
                }
            }
            $content=preg_replace_callback('/<form\b[^>]*>/i',fn($m)=>$m[0].'<input type="hidden" name="csrf_token" value="'.htmlspecialchars(csrf_token(),ENT_QUOTES).'">',$content);
            $data['content']=$content;
            $html=$this->view('layouts/main',$data);
            if($editing && TenantContext::editorMode()==='website_first') {
                $html=EditableFields::annotate($html,$document);
                $store=new BuilderStore($this->db,TenantContext::id());$state=$store->state();$live=$store->published();$live['presentation']??=$document['presentation'];
                $values=[];foreach(EditableFields::schema() as $id=>$spec)$values[$id]=EditableFields::value($document,$spec);
                $boot=['api'=>url('editor-api/'),'csrf'=>csrf_token(),'revision'=>(int)$state['revision'],'values'=>$values,'published'=>SiteDocument::encode($document)===SiteDocument::encode($live)];
                $html=str_replace('</body>',$this->view('builder/editor',['boot'=>$boot]).'</body>',$html);
            }elseif($owner&&!$editing){
                $html=str_replace('</body>','<a data-owner-edit class="btn btn-primary position-fixed bottom-0 end-0 m-3" style="z-index:1050" href="'.htmlspecialchars(url('edit?page='.$pageKey),ENT_QUOTES).'">Edit website</a></body>',$html);
            }
            return $html;
        });
    }
}
