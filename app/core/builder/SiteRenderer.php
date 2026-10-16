<?php
declare(strict_types=1);
require_once __DIR__.'/SectionRegistry.php';
require_once __DIR__.'/SiteDocument.php';
final class SiteRenderer {
    public function __construct(private PDO $db) {}
    public function collections(string $type): array {
        $spec=SectionRegistry::get($type);$table=$spec['collection']??null;
        if(!$table)return [];
        try {
            $filter="status='published'";
            if($table==='skills')$filter.=" AND category_id IN (SELECT id FROM skill_categories WHERE is_visible=1)";
            return $this->db->query("SELECT * FROM `".$table."` WHERE ".$filter." ORDER BY sort_order,id")->fetchAll(PDO::FETCH_ASSOC);
        }catch(PDOException $e){if($e->getCode()==='42S02')return [];throw $e;}
    }
    public function render(array $document,string $pageKey,bool $editing=false,bool $owner=false): string {
        SiteDocument::validate($document);
        $page=$document['pages'][$pageKey]??null;
        if(!$page||(!$page['visible']&&!$editing))throw new RuntimeException('Page not found.',404);
        $profile=$document['profile'];$theme=$document['theme'];
        $e=fn($v)=>htmlspecialchars((string)$v,ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8');
        $href=fn($v)=>SiteDocument::validUrl((string)$v)?navbar_build_nav_url((string)$v):'#';
        $image=function($path) {
            if(!is_string($path)||$path===''||preg_match('/[\x00-\x20\\\\]/',$path)||str_contains($path,'..'))return '';
            if(preg_match('#^https?://#i',$path))return SiteDocument::validUrl($path)?$path:'';
            if(!str_starts_with(ltrim($path,'/'),'assets/'))return '';
            return media_url($path);
        };
        $sections=$page['sections'];usort($sections,fn($a,$b)=>$a['order']<=>$b['order']);
        $rendered=[];
        foreach($sections as $section){
            $spec=SectionRegistry::get($section['type']);
            $items=$this->collections($section['type']);
            $empty=isset($spec['collection'])&&!$items;
            if($section['type']==='about')$empty=trim($profile['bio'])==='';
            if($section['type']==='contact')$empty=$profile['email']===''&&$profile['phone']===''&&$profile['location']===''&&!$profile['social'];
            if((!$section['visible']||$empty)&&!$editing)continue;
            $settings=$section['settings'];
            ob_start();
            require ROOT_PATH.'/app/views/builder/section.php';
            $rendered[]=ob_get_clean();
        }
        ob_start();require ROOT_PATH.'/app/views/builder/site.php';return ob_get_clean();
    }
}
