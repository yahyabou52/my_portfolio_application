<?php

require_once ROOT_PATH . '/app/core/BaseModel.php';

class ProjectImage extends BaseModel {
    protected $table = 'project_images';
    protected $fillable = [
        'project_id',
        'image_path',
        'caption',
        'sort_order'
    ];

    public function getByProject($projectId) {
        return $this->where(['project_id' => $projectId], 'sort_order ASC');
    }

    public function deleteByProject($projectId) {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE project_id = :project_id");
        $stmt->bindValue(':project_id', $projectId, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
