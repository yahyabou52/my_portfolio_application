<?php
declare(strict_types=1);
require_once __DIR__.'/SiteDocument.php';
final class BuilderConflict extends RuntimeException {}
final class BuilderStore {
    public function __construct(private PDO $db, private int $siteId) {}
    public function state(bool $lock=false): array {
        $row=$this->db->query('SELECT * FROM builder_state WHERE id=1'.($lock?' FOR UPDATE':''))->fetch(PDO::FETCH_ASSOC);
        if(!$row||(int)$row['site_id']!==$this->siteId)throw new RuntimeException('Website state not found.');
        return $row;
    }
    public function authorize(array $actor): void {
        $state=$this->state();
        $q=$this->db->prepare('SELECT role FROM admin_users WHERE id=?');$q->execute([(int)($actor['id']??0)]);$role=$q->fetchColumn();
        if($role!=='superadmin'||($actor['site_id']??null)!==$this->siteId||($actor['role']??null)!=='superadmin'||(int)($actor['id']??0)!==(int)$state['owner_id'])throw new RuntimeException('Only the website owner can make changes.',403);
    }
    public function published(): array {
        $q=$this->db->prepare('SELECT document_json FROM builder_versions WHERE id=?');
        $q->execute([$this->state()['published_version_id']]);
        $json=$q->fetchColumn();
        if(!$json)throw new RuntimeException('Published version missing.');
        return json_decode($json,true,512,JSON_THROW_ON_ERROR);
    }
    public function draft(array $actor): array { $this->authorize($actor); return json_decode($this->state()['draft_json'],true,512,JSON_THROW_ON_ERROR); }
    public function saveDraft(array $actor,int $revision,array $document): int {
        return $this->mutate($actor,$revision,function($state)use($document){
            $json=SiteDocument::encode($document);$this->validateMedia($document);
            if($state['last_save_at']!==null&&microtime(true)-(float)$state['last_save_at']<0.5)throw new RuntimeException('Please wait before saving again.',429);
            $q=$this->db->prepare('UPDATE builder_state SET draft_json=?, revision=revision+1,last_save_at=? WHERE id=1');
            $q->execute([$json,microtime(true)]);
        });
    }
    public function publish(array $actor,int $revision): int {
        return $this->mutate($actor,$revision,function($state)use($actor){
            $doc=json_decode($state['draft_json'],true,512,JSON_THROW_ON_ERROR);$json=SiteDocument::encode($doc);$this->validateMedia($doc);
            $q=$this->db->prepare('INSERT INTO builder_versions(document_json,created_by) VALUES (?,?)');$q->execute([$json,$actor['id']]);
            $version=(int)$this->db->lastInsertId();
            $this->db->prepare('UPDATE builder_state SET published_version_id=?,revision=revision+1 WHERE id=1')->execute([$version]);
        });
    }
    public function discard(array $actor,int $revision): int {
        return $this->mutate($actor,$revision,function($state){
            $q=$this->db->prepare('UPDATE builder_state SET draft_json=(SELECT document_json FROM builder_versions WHERE id=?),revision=revision+1,last_save_at=NULL WHERE id=1');
            $q->execute([$state['published_version_id']]);
        });
    }
    private function mutate(array $actor,int $revision,callable $operation): int {
        $this->authorize($actor);$this->db->beginTransaction();
        try {
            $state=$this->state(true);
            if($state['editor_mode']!=='website_first')throw new RuntimeException('This website still uses the legacy editor.',409);
            $this->authorize($actor);
            if((int)$state['revision']!==$revision)throw new BuilderConflict('This page changed in another session. Reload the latest draft before saving.',409);
            $operation($state);$next=(int)$state['revision']+1;$this->db->commit();return $next;
        }catch(Throwable $e){if($this->db->inTransaction())$this->db->rollBack();throw $e;}
    }
    private function validateMedia(array $doc): void {
        $photo=$doc['profile']['photo'];if($photo==='')return;
        $q=$this->db->prepare("SELECT id FROM media_files WHERE file_path=? AND file_type='image'");$q->execute([$photo]);
        if(!$q->fetchColumn()){
            // Imported, existing images are retained, but may not be replaced by arbitrary paths.
            $existing=$this->published()['profile']['photo']??'';
            if($photo!==$existing)throw new InvalidArgumentException('Choose an image from this website media library.');
        }
    }
}
