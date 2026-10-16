<?php
declare(strict_types=1);
final class CollectionStore {
    public function __construct(private PDO $db) {}
    public static function schemas(): array {
        return [
            'projects'=>['title'=>'text:200','short_description'=>'textarea:500','description'=>'textarea:20000','category'=>'text:120','main_image_path'=>'image:255','main_image_alt'=>'text:150','client_name'=>'text:150','project_url'=>'url:255','technologies'=>'list:2000','sort_order'=>'number:100000'],
            'skills'=>['name'=>'text:150','category'=>'text:150','proficiency_level'=>'number:100','sort_order'=>'number:100000'],
            'experience'=>['title'=>'text:200','organization'=>'text:200','date_range'=>'text:120','description'=>'textarea:20000','tags'=>'list:2000','is_education'=>'number:1','sort_order'=>'number:100000'],
            'services'=>['title'=>'text:200','description'=>'textarea:20000','icon'=>'icon:120','price_label'=>'text:120','price_amount'=>'decimal:1000000','sort_order'=>'number:100000'],
            'testimonials'=>['client_name'=>'text:150','client_position'=>'text:150','client_company'=>'text:150','testimonial_text'=>'textarea:20000','image_path'=>'image:255','rating'=>'number:5','sort_order'=>'number:100000']
        ];
    }
    private function table(string $type): string {
        if(!isset(self::schemas()[$type]))throw new InvalidArgumentException('Unknown collection.');
        return $type==='experience'?'timeline_items':$type;
    }
    public static function version(array $row): string {return hash('sha256',json_encode($row,JSON_THROW_ON_ERROR));}
    public function listing(string $type): array {
        $table=$this->table($type);$rows=$this->db->query("SELECT * FROM `$table` ORDER BY sort_order,id")->fetchAll(PDO::FETCH_ASSOC);
        foreach($rows as &$row){$row['_version']=self::version($row);if($type==='skills'){$q=$this->db->prepare('SELECT title FROM skill_categories WHERE id=?');$q->execute([$row['category_id']]);$row['category']=$q->fetchColumn()?:'Skills';}}unset($row);
        return ['items'=>$rows,'schema'=>self::schemas()[$type]];
    }
    public function save(array $input): array {
        $type=(string)($input['type']??'');$table=$this->table($type);$id=(int)($input['id']??0);
        $status=$input['status']??'draft';if(!in_array($status,['draft','published'],true))throw new InvalidArgumentException('Invalid item status.');
        $this->db->beginTransaction();
        try{
            $existing=null;
            if($id){$q=$this->db->prepare("SELECT * FROM `$table` WHERE id=? FOR UPDATE");$q->execute([$id]);$existing=$q->fetch(PDO::FETCH_ASSOC);if(!$existing)throw new RuntimeException('Item not found.',404);if(!hash_equals(self::version($existing),(string)($input['version']??'')))throw new RuntimeException('This item changed in another session. Reopen it before saving.',409);}
            if(($input['operation']??'')==='delete'){
                if(!$id)throw new InvalidArgumentException('Choose an item.');
                $this->db->prepare("DELETE FROM `$table` WHERE id=?")->execute([$id]);$this->db->commit();return ['deleted'=>true];
            }
            $data=['status'=>$status];$fields=$input['fields']??[];
            if(!is_array($fields))throw new InvalidArgumentException('Invalid item.');
            foreach(self::schemas()[$type] as $key=>$rule){
                if(!array_key_exists($key,$fields))continue;
                [$kind,$max]=explode(':',$rule);$value=$fields[$key];
                if(!is_scalar($value)||strlen((string)$value)>(int)$max&& !in_array($kind,['number','decimal'],true))throw new InvalidArgumentException('Invalid '.$key.'.');
                $value=trim((string)$value);
                if($kind==='number'||$kind==='decimal'){
                    if($value===''&&$kind==='decimal')$value=null;
                    elseif(!is_numeric($value)||(float)$value<0||(float)$value>(int)$max||($kind==='number'&&(float)$value!=(int)$value))throw new InvalidArgumentException('Invalid '.$key.'.');
                }
                if($kind==='url'&&$value!==''&&(!preg_match('/^(https?:|mailto:|tel:)/i',$value)||!SiteDocument::validUrl($value)))throw new InvalidArgumentException('Use an http, https, mailto or tel link.');
                if($kind==='icon'&&$value!==''&&!preg_match('/^[a-zA-Z0-9 _-]+$/',$value))throw new InvalidArgumentException('Invalid icon.');
                if($kind==='image'&&$value!==''&&$value!==($existing[$key]??'')){
                    $q=$this->db->prepare("SELECT id FROM media_files WHERE file_path=? AND file_type='image'");$q->execute([$value]);if(!$q->fetchColumn())throw new InvalidArgumentException('Choose an image uploaded to this website.');
                }
                if($kind==='list')$value=json_encode(array_values(array_filter(array_map('trim',explode(',',$value)))),JSON_THROW_ON_ERROR);
                $data[$key]=$value;
            }
            $required=['projects'=>'title','skills'=>'name','experience'=>'title','services'=>'title','testimonials'=>'client_name'][$type];
            if(trim((string)($data[$required]??$existing[$required]??''))==='')throw new InvalidArgumentException('Enter a '.$required.'.');
            if($type==='testimonials'&&trim((string)($data['testimonial_text']??$existing['testimonial_text']??''))==='')throw new InvalidArgumentException('Enter the testimonial.');
            if($type==='projects'&&!$id){$data['slug']=(new Project())->generateSlug($data['title']);$data['featured']=1;}
            if($type==='services'&&!$id){$data['homepage_featured']=1;$data['homepage_is_visible']=1;}
            if($type==='skills'){
                $category=$data['category']??'Skills';unset($data['category']);
                $q=$this->db->prepare('SELECT id FROM skill_categories WHERE title=? LIMIT 1');$q->execute([$category]);$categoryId=$q->fetchColumn();
                if(!$categoryId){$this->db->prepare("INSERT INTO skill_categories(title,icon_class,is_visible) VALUES (?,'bi bi-stars',1)")->execute([$category]);$categoryId=$this->db->lastInsertId();}
                $data['category_id']=$categoryId;$data['is_visible']=$status==='published'?1:0;
            }
            if($id){$assign=implode(',',array_map(fn($k)=>"`$k`=?",array_keys($data)));$this->db->prepare("UPDATE `$table` SET $assign WHERE id=?")->execute([...array_values($data),$id]);}
            else{$columns=implode(',',array_map(fn($k)=>"`$k`",array_keys($data)));$marks=implode(',',array_fill(0,count($data),'?'));$this->db->prepare("INSERT INTO `$table` ($columns) VALUES ($marks)")->execute(array_values($data));$id=(int)$this->db->lastInsertId();}
            $this->db->commit();return ['id'=>$id,'status'=>$status];
        }catch(Throwable $e){if($this->db->inTransaction())$this->db->rollBack();throw $e;}
    }
}
