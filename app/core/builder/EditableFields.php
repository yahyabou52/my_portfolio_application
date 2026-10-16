<?php
declare(strict_types=1);
final class EditableFields {
    public static function schema(array $doc=[]): array {
        $fields=[];
        foreach(['name','profession','intro','bio','email','phone','location'] as $key)$fields['profile.'.$key]=['path'=>['profile',$key],'kind'=>'text'];
        $fields['profile.social']=['path'=>['profile','social'],'kind'=>'social'];
        $fields['profile.photo']=['path'=>['profile','photo'],'kind'=>'image'];
        $fields['name.first']=['path'=>['profile','name'],'kind'=>'text','part'=>'first'];
        $fields['name.rest']=['path'=>['profile','name'],'kind'=>'text','part'=>'rest'];
        foreach(['hero_title','hero_subtitle','hero_intro_prefix','hero_intro_suffix','scroll_indicator_text','hero_background_image_alt','hero_primary_cta_text','hero_secondary_cta_text'] as $key)$fields['hero.'.$key]=['path'=>['presentation','hero',$key],'kind'=>'text'];
        foreach(['hero_primary_cta_url','hero_secondary_cta_url'] as $key)$fields['hero.'.$key]=['path'=>['presentation','hero',$key],'kind'=>'url'];
        $fields['hero.image']=['path'=>['presentation','hero','hero_background_image_path'],'kind'=>'image'];
        foreach(['headline','greeting','philosophy','image_alt'] as $key)$fields['about.'.$key]=['path'=>['presentation','about',$key],'kind'=>'text'];
        foreach(WebsiteContent::manifest() as $key=>$field)if($field['type']==='text')$fields['copy:'.$key]=['path'=>['presentation','values','content.'.$key],'kind'=>'text','default'=>$field['default']];
        foreach($doc['presentation']['hero_stats']??[] as $i=>$stat)foreach(['value','label'] as $key)$fields['stat.'.$i.'.'.$key]=['path'=>['presentation','hero_stats',$i,$key],'kind'=>'text'];
        foreach(['site_description','nav_cta_text','nav_cta_url','site_logo','site_favicon'] as $key)$fields['brand.'.$key]=['path'=>['presentation','global','siteSettings',$key],'kind'=>str_ends_with($key,'url')?'url':(in_array($key,['site_logo','site_favicon'],true)?'image':'text')];
        foreach(['footer_text','footer_additional_text'] as $key)$fields['footer.'.$key]=['path'=>['presentation','global','footerSettings',$key],'kind'=>'text'];
        foreach($doc['presentation']['global']['navigationMenu']??[] as $i=>$item)foreach(['title'=>'text','url'=>'url'] as $key=>$kind)$fields['navigation.'.$i.'.'.$key]=['path'=>['presentation','global','navigationMenu',$i,$key],'kind'=>$kind];
        foreach($doc['presentation']['global']['footerSettings']['footer_links']??[] as $i=>$item)foreach(['label'=>'text','url'=>'url'] as $key=>$kind)$fields['footerlink.'.$i.'.'.$key]=['path'=>['presentation','global','footerSettings','footer_links',$i,$key],'kind'=>$kind];
        foreach(['title','subtitle','primary_cta_text','secondary_cta_text','primary_cta_url','secondary_cta_url'] as $key)$fields['cta.'.$key]=['path'=>['presentation','pages','home','sections','page_cta',$key],'kind'=>str_ends_with($key,'url')?'url':'text'];
        foreach(WebsiteContent::manifest() as $key=>$field)if($field['type']==='text')$fields['link:'.$key]=['path'=>['presentation','links',$key],'kind'=>'url'];
        if($doc&&!isset($doc['compatibility'])){$fields['hero.hero_title']['path']=['profile','profession'];$fields['hero.image']['path']=['profile','photo'];}
        return $fields;
    }
    public static function value(array $doc,array $spec): string {
        $v=$doc;foreach($spec['path'] as $key)$v=is_array($v)?($v[$key]??null):null;
        $value=$spec['kind']==='social'?json_encode($v??[],JSON_THROW_ON_ERROR):(string)($v??$spec['default']??'');
        if(isset($spec['part'])){$parts=explode(' ',trim($value),2);return $spec['part']==='first'?$parts[0]:($parts[1]??'');}
        return $value;
    }
    public static function apply(array &$doc,string $id,string $value): void {
        $spec=self::schema($doc)[$id]??throw new InvalidArgumentException('Unknown editable field.');
        if(strlen($value)>20000)throw new InvalidArgumentException('This text is too long.');
        if($spec['kind']==='url'&&$value!==''&&(!preg_match('/^(https?:|mailto:|tel:)/i',$value)||!SiteDocument::validUrl($value)))throw new InvalidArgumentException('Use an http, https, mailto or tel link.');
        if($spec['kind']==='social'){$value=json_decode($value,true,512,JSON_THROW_ON_ERROR);if(!is_array($value)||count($value)>20)throw new InvalidArgumentException('Invalid social links.');foreach($value as $link)if(!is_array($link)||!preg_match('/^[a-zA-Z0-9_-]{1,40}$/',(string)($link['platform']??''))||!preg_match('/^(https?:|mailto:|tel:)/i',(string)($link['url']??''))||!SiteDocument::validUrl((string)$link['url']))throw new InvalidArgumentException('Use valid social links with http, https, mailto or tel.');}
        if(isset($spec['part'])){$parts=explode(' ',trim($doc['profile']['name']),2);$parts[$spec['part']==='first'?0:1]=trim($value);$value=trim(implode(' ',$parts));}
        $target=&$doc;foreach($spec['path'] as $key){if(!is_array($target))$target=[];$target=&$target[$key];}$target=$value;
        if($id==='hero.image'&&!empty($doc['compatibility']))$doc['presentation']['hero']['hero_background_image_url']=media_url($value);
    }
    public static function annotate(string $html,array $doc,bool $editing=true): string {
        $tags=[];$i=0;
        $marked=preg_replace_callback('/<[a-zA-Z][^>]*>/s',function($m)use(&$tags,&$i){$id=(string)++$i;$tags[$id]=$m[0];return substr($m[0],0,-1).' data-wfe-node="'.$id.'">';},$html);
        $dom=new DOMDocument();$previous=libxml_use_internal_errors(true);$dom->loadHTML('<?xml encoding="UTF-8">'.$marked);libxml_clear_errors();libxml_use_internal_errors($previous);$xpath=new DOMXPath($dom);$attributes=[];
        $selectors=[
            'hero.hero_title'=>['hero-title'],'hero.hero_subtitle'=>['hero-subtitle'],'profile.intro'=>['hero-description'],
            'hero.hero_intro_prefix'=>['hero-kicker-prefix'],'hero.hero_intro_suffix'=>['hero-kicker-suffix'],'name.first'=>['hero-kicker-name-first'],'name.rest'=>['hero-kicker-name-last'],
            'hero.scroll_indicator_text'=>['scroll-text'],'about.headline'=>['about-title'],'profile.bio'=>['about-bio']
        ];
        $set=function(DOMElement $node,string $id,string $kind='text',?string $link=null)use(&$attributes){
            $key=$node->getAttribute('data-wfe-node');if(!$key)return;
            $attributes[$key]=['data-edit-field'=>$id,'data-edit-kind'=>$kind];
            if($link)$attributes[$key]['data-edit-link']=$link;
        };
        foreach($selectors as $id=>$classes)foreach($classes as $class)foreach($xpath->query('//*[contains(concat(" ",normalize-space(@class)," ")," '.$class.' ")]') as $node)$set($node,empty($doc['compatibility'])&&$id==='hero.hero_title'?'profile.profession':$id);
        foreach($xpath->query('//*[contains(concat(" ",@class," ")," hero-actions ")]/a') as $index=>$node)$set($node,$index===0?'hero.hero_primary_cta_text':'hero.hero_secondary_cta_text','button',$index===0?'hero.hero_primary_cta_url':'hero.hero_secondary_cta_url');
        foreach($xpath->query('//*[contains(concat(" ",@class," ")," hero-image-wrapper ")]/img') as $node)$set($node,empty($doc['compatibility'])?'profile.photo':'hero.image','image');
        foreach($xpath->query('//*[contains(concat(" ",@class," ")," about-image ")]/img') as $node)$set($node,'profile.photo','image');
        foreach($xpath->query('//img') as $node){
            if(isset($attributes[$node->getAttribute('data-wfe-node')]))continue;
            foreach(self::schema($doc) as $id=>$spec)if($spec['kind']==='image'){$value=self::value($doc,$spec);if($value!==''&&$node->getAttribute('src')===media_url($value)){$set($node,$id,'image');break;}}
        }
        $texts=[];foreach(self::schema($doc) as $id=>$spec)if($spec['kind']==='text'&&!str_starts_with($id,'hero.')){$value=html_entity_decode(self::value($doc,$spec),ENT_QUOTES,'UTF-8');if(trim($value)!=='')$texts[trim($value)]=$id;}
        foreach($xpath->query('//text()[normalize-space(.)!=""]') as $node){
            $text=trim($node->nodeValue);$parent=$node->parentNode;
            if(!isset($texts[$text])||!$parent instanceof DOMElement||in_array(strtolower($parent->tagName),['script','style','option','title'],true))continue;
            $key=$parent->getAttribute('data-wfe-node');if(isset($attributes[$key]))continue;
            $id=$texts[$text];$link=null;
            if(strtolower($parent->tagName)==='a'){
                if(str_starts_with($id,'copy:'))$link='link:'.substr($id,5);
                elseif(str_starts_with($id,'navigation.'))$link=preg_replace('/title$/','url',$id);
                elseif(str_starts_with($id,'footerlink.'))$link=preg_replace('/label$/','url',$id);
                elseif($id==='brand.nav_cta_text')$link='brand.nav_cta_url';
                elseif(str_starts_with($id,'cta.')&&str_ends_with($id,'_text'))$link=substr($id,0,-5).'_url';
            }
            $set($parent,$id,$link?'button':'text',$link);
        }
        $links=[];foreach($attributes as $key=>$attrs)if(isset($attrs['data-edit-link'])){
            $spec=self::schema($doc)[$attrs['data-edit-link']]??null;$value=$spec?self::value($doc,$spec):'';
            if($value!==''&&SiteDocument::validUrl($value))$links[$key]=navbar_build_nav_url($value);
        }
        // Emit original markup; persisted link overrides are plain public content, never editor controls.
        $i=0;return preg_replace_callback('/<[a-zA-Z][^>]*>/s',function($m)use(&$i,$attributes,$editing,$links){$id=(string)++$i;if(!isset($attributes[$id]))return $m[0];if(isset($links[$id]))$m[0]=preg_replace_callback('/href="[^"]*"/',fn()=> 'href="'.htmlspecialchars($links[$id],ENT_QUOTES).'"',$m[0]);if(!$editing)return $m[0];$attrs='';foreach($attributes[$id] as $key=>$value)$attrs.=' '.$key.'="'.htmlspecialchars($value,ENT_QUOTES).'"';return substr($m[0],0,-1).$attrs.'>';},$html);
    }
}
