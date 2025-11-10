<?php

require_once ROOT_PATH . '/app/core/BaseModel.php';

class HeroStat extends BaseModel {
    protected $table = 'hero_stats';
    protected $fillable = [
        'hero_id',
        'label',
        'value',
        'sort_order',
        'is_active'
    ];

    public function getActive($limit = null) {
        return $this->where(['is_active' => 1], 'sort_order ASC', $limit);
    }

    public function updateSort($id, $sortOrder) {
        return $this->update($id, ['sort_order' => $sortOrder]);
    }

    public function getByHero(int $heroId, bool $onlyActive = true): array {
        $conditions = ['hero_id' => $heroId];
        if ($onlyActive) {
            $conditions['is_active'] = 1;
        }

        return $this->where($conditions, 'sort_order ASC, id ASC');
    }

    public function getMaxSortOrder(int $heroId): int {
        $stmt = $this->db->prepare("SELECT COALESCE(MAX(sort_order), 0) FROM {$this->table} WHERE hero_id = :hero_id");
        $stmt->bindValue(':hero_id', $heroId, PDO::PARAM_INT);
        $stmt->execute();

        return (int)$stmt->fetchColumn();
    }
}
