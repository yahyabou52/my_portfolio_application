<?php
declare(strict_types=1);
final class DraftStructure {
    public static function sections(array $doc,string $page): array {
        if(!isset($doc['pages'][$page]))throw new InvalidArgumentException('Unknown page.');
        if(empty($doc['compatibility']))return $doc['pages'][$page]['sections'];
        $items=[];$values=$doc['presentation']['values']??[];
        foreach(WebsiteContent::manifest() as $key=>$field) {
            if(!str_starts_with($key,$page.'.section.')||str_ends_with($key,'.order'))continue;
            $items[]=['id'=>$key,'type'=>$field['label']??substr($key,strrpos($key,'.')+1),'variant'=>'original','visible'=>($values['content.'.$key]??'1')!=='0','order'=>(int)($values['content.'.$key.'.order']??count($items)*10)];
        }
        usort($items,fn($a,$b)=>$a['order']<=>$b['order']);return $items;
    }
    public static function apply(array &$doc,array $input): void {
        $page=(string)($input['page']??'home');$op=$input['operation']??'';$id=$input['id']??'';
        if($op==='page'){
            if(!in_array($page,['home','about','portfolio','services','contact'],true))throw new InvalidArgumentException('Unknown page.');
            $title=trim((string)($input['title']??''));
            if($title===''||mb_strlen($title)>160)throw new InvalidArgumentException('Use a page title between 1 and 160 characters.');
            if(!is_bool($input['visible']??null))throw new InvalidArgumentException('Invalid page visibility.');
            if($page==='home'&&!$input['visible'])throw new InvalidArgumentException('Your home page must remain visible.');
            if(!isset($doc['pages'][$page])){
                if(!empty($doc['compatibility']))throw new InvalidArgumentException('This preserved page is not available.');
                $types=['about'=>['about','skills','experience'],'portfolio'=>['projects'],'services'=>['services'],'contact'=>['contact']][$page]??[];
                $sections=[];
                foreach($types as $i=>$type){$spec=SectionRegistry::get($type);$sections[]=['id'=>$type.'-'.$page,'type'=>$type,'variant'=>$spec['variants'][0],'order'=>$i*10,'visible'=>true,'settings'=>[],'collection'=>$spec['collection']??null];}
                $doc['pages'][$page]=['title'=>$title,'visible'=>$input['visible'],'sections'=>$sections];
            }
            $doc['pages'][$page]['title']=$title;$doc['pages'][$page]['visible']=$input['visible'];return;
        }
        if($op==='theme'){
            foreach(['primary_color','background_color'] as $key)if(isset($input[$key])){
                if(!preg_match('/^#[0-9a-fA-F]{6}$/',(string)$input[$key]))throw new InvalidArgumentException('Invalid color.');
                $doc['presentation']['values'][$key]=$input[$key];
            }
            if(isset($input['font'])){if(!in_array($input['font'],['original','Inter','Playfair Display'],true))throw new InvalidArgumentException('Unknown font.');$doc['presentation']['values']['builder_font']=$input['font'];}
            return;
        }
        $sections=self::sections($doc,$page);
        $index=array_search($id,array_column($sections,'id'),true);
        if($op==='add'){
            if(!empty($doc['compatibility']))throw new InvalidArgumentException('Restore a hidden original section on this preserved page.');
            $type=(string)($input['type']??'');$spec=SectionRegistry::get($type);
            if(count($sections)>=30)throw new InvalidArgumentException('Maximum section count reached.');
            // Existing templates use stable IDs. One instance per type avoids duplicate form/accordion IDs.
            foreach($sections as $s)if($s['type']===$type)throw new InvalidArgumentException('This section already exists. Show it from the section list.');
            $sections[]=['id'=>$type.'-'.bin2hex(random_bytes(4)),'type'=>$type,'variant'=>$spec['variants'][0],'order'=>count($sections)*10,'visible'=>true,'settings'=>[],'collection'=>$spec['collection']??null];
        }else{
            if($index===false)throw new InvalidArgumentException('Unknown section.');
            if($op==='visibility')$sections[$index]['visible']=!$sections[$index]['visible'];
            elseif($op==='remove'){if(!empty($doc['compatibility']))$sections[$index]['visible']=false;else array_splice($sections,$index,1);}
            elseif($op==='move'){
                $target=$index+(($input['direction']??'')==='up'?-1:1);
                if(isset($sections[$target]))[$sections[$index],$sections[$target]]=[$sections[$target],$sections[$index]];
            }elseif($op==='place'){
                $targetId=$input['target']??'';
                if($targetId===$id)throw new InvalidArgumentException('Choose a different target section.');
                $target=array_search($targetId,array_column($sections,'id'),true);
                if($target===false||!in_array($input['position']??'', ['before','after'],true))throw new InvalidArgumentException('Invalid section position.');
                $moving=$sections[$index];array_splice($sections,$index,1);
                $target=array_search($targetId,array_column($sections,'id'),true);
                array_splice($sections,$target+($input['position']==='after'?1:0),0,[$moving]);
            }elseif($op==='variant'){
                $variant=$input['variant']??'';
                $allowed=SectionRegistry::availableVariants($sections[$index]['type']);
                if(!in_array($variant,$allowed,true))throw new InvalidArgumentException('Only existing design layouts are available.');
                $sections[$index]['variant']=$variant;
            }else throw new InvalidArgumentException('Unknown section operation.');
        }
        foreach($sections as $i=>&$section)$section['order']=$i*10;unset($section);
        if(!empty($doc['compatibility']))foreach($sections as $s){$doc['presentation']['values']['content.'.$s['id']]=$s['visible']?'1':'0';$doc['presentation']['values']['content.'.$s['id'].'.order']=(string)$s['order'];}
        else $doc['pages'][$page]['sections']=$sections;
    }
}
