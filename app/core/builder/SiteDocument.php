<?php
declare(strict_types=1);
require_once __DIR__.'/SectionRegistry.php';
final class SiteDocument {
    public static function fresh(array $answers): array {
        $name=trim((string)($answers['name']??'Your name'));
        $profile=['name'=>$name,'profession'=>(string)($answers['profession']??''),'intro'=>(string)($answers['intro']??''),'bio'=>(string)($answers['bio']??''),'photo'=>'','email'=>(string)($answers['contact_email']??''),'phone'=>'','location'=>'','social'=>[]];
        $sections=[];
        foreach(['hero','about','projects','skills','experience','contact'] as $i=>$type) {
            $spec=SectionRegistry::get($type);
            $sections[]=['id'=>$type.'-1','type'=>$type,'variant'=>$spec['variants'][0],'order'=>$i*10,'visible'=>true,'settings'=>['heading'=>ucfirst($type)],'collection'=>$spec['collection']??null];
        }
        if(isset($answers['sections'])&&is_array($answers['sections']))foreach($sections as &$section)$section['visible']=in_array($section['type'],$answers['sections'],true);unset($section);
        $sections[0]['settings']=['heading'=>'','button_label'=>'Get in touch','button_url'=>'mailto:'.$profile['email']];
        if($profile['email']==='')$sections[0]['settings']['button_url']='';
        return ['schema_version'=>1,'profile'=>$profile,'theme'=>['color'=>'#7B3FE4','background'=>'#ffffff','font'=>'system'],'pages'=>['home'=>['title'=>$name,'visible'=>true,'sections'=>$sections]]];
    }
    // Relative destinations and anchors are internal routes, never arbitrary URL schemes.
    public static function validUrl(string $url): bool {
        if($url==='')return true;
        if(preg_match('/[\x00-\x20\x7f\\\\]/',$url))return false;
        if(preg_match('#^/(?!/)#',$url)||preg_match('/^#[a-zA-Z][a-zA-Z0-9_-]*$/',$url))return true;
        $scheme=strtolower((string)parse_url($url,PHP_URL_SCHEME));
        if(in_array($scheme,['http','https'],true))return filter_var($url,FILTER_VALIDATE_URL)!==false;
        if($scheme==='mailto')return filter_var(substr($url,7),FILTER_VALIDATE_EMAIL)!==false;
        if($scheme==='tel')return (bool)preg_match('/^tel:\+?[0-9().-]{3,40}$/i',$url);
        return false;
    }
    public static function validate(array $doc): void {
        if(($doc['schema_version']??null)!==1||!is_array($doc['profile']??null)||!is_array($doc['pages']??null))throw new InvalidArgumentException('Invalid site document.');
        if(count($doc['pages'])>20)throw new InvalidArgumentException('Too many pages.');
        foreach(['name','profession','intro','bio','photo','email','phone','location'] as $key){
            if(!is_string($doc['profile'][$key]??null)||strlen($doc['profile'][$key])>20000)throw new InvalidArgumentException('Invalid profile field: '.$key);
        }
        if($doc['profile']['email']!==''&&!filter_var($doc['profile']['email'],FILTER_VALIDATE_EMAIL))throw new InvalidArgumentException('Invalid email.');
        if(!is_array($doc['profile']['social']??null))throw new InvalidArgumentException('Invalid social links.');
        foreach($doc['profile']['social'] as $link)if(!is_array($link)||!self::validUrl((string)($link['url']??'')))throw new InvalidArgumentException('Invalid social link.');
        foreach(['color','background'] as $key)if(!preg_match('/^#[0-9a-fA-F]{6}$/',$doc['theme'][$key]??''))throw new InvalidArgumentException('Invalid theme color.');
        if(!in_array($doc['theme']['font']??'', ['system','serif','mono'],true))throw new InvalidArgumentException('Unknown font.');
        foreach($doc['pages'] as $key=>$page){
            if(!preg_match('/^[a-z][a-z0-9_-]{0,40}$/',$key)||!is_string($page['title']??null)||!is_bool($page['visible']??null)||!is_array($page['sections']??null)||count($page['sections'])>50)throw new InvalidArgumentException('Invalid page.');
            $ids=[];
            foreach($page['sections'] as $section){
                $spec=SectionRegistry::get($section['type']??'');
                if(!preg_match('/^[a-zA-Z0-9_-]{1,80}$/',$section['id']??'')||isset($ids[$section['id']]))throw new InvalidArgumentException('Invalid section ID.');
                $ids[$section['id']]=true;
                if(!in_array($section['variant']??'', $spec['variants'],true)||!is_bool($section['visible']??null)||!is_int($section['order']??null))throw new InvalidArgumentException('Invalid section layout.');
                if(($section['collection']??null)!==($spec['collection']??null))throw new InvalidArgumentException('Invalid collection reference.');
                foreach($section['settings']??[] as $field=>$value){
                    if(!isset($spec['fields'][$field])||!is_string($value)||strlen($value)>20000)throw new InvalidArgumentException('Invalid section field.');
                    if($spec['fields'][$field]==='url'&&!self::validUrl($value))throw new InvalidArgumentException('Invalid link.');
                }
            }
        }
    }
    public static function encode(array $doc): string { self::validate($doc);return json_encode($doc,JSON_THROW_ON_ERROR|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES); }
}
