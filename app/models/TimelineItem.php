<?php

require_once ROOT_PATH . '/app/core/BaseModel.php';

class TimelineItem extends BaseModel {
    protected $table = 'timeline_items';
    protected $fillable = [
        'title',
        'organization',
        'date_range',
        'description',
        'tags',
        'is_education',
        'sort_order',
        'status'
    ];

    public function getPublished() {
        return $this->where(['status' => 'published'], 'sort_order ASC');
    }

    public function getMaxSortOrder(): int {
        $stmt = $this->db->query("SELECT COALESCE(MAX(sort_order), 0) FROM {$this->table}");
        return (int)$stmt->fetchColumn();
    }
}
