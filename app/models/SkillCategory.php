<?php

require_once ROOT_PATH . '/app/core/BaseModel.php';

class SkillCategory extends BaseModel {
    protected $table = 'skill_categories';
    protected $fillable = [
        'title',
        'icon_class',
        'sort_order',
        'is_visible'
    ];

    public function getOrdered(bool $includeHidden = false): array {
        $orderBy = 'sort_order ASC, title ASC';

        if ($includeHidden) {
            return $this->all($orderBy);
        }

        return $this->where(['is_visible' => 1], $orderBy);
    }

    public function deleteMissing(array $ids): void {
        if (empty($ids)) {
            $this->db->exec("DELETE FROM {$this->table}");
            return;
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $sql = "DELETE FROM {$this->table} WHERE id NOT IN ({$placeholders})";
        $statement = $this->db->prepare($sql);
        $statement->execute($ids);
    }
}
