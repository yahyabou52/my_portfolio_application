<?php

require_once ROOT_PATH . '/app/core/BaseModel.php';

class ServiceProcessStep extends BaseModel {
    protected $table = 'service_process_steps';
    protected $fillable = [
        'title',
        'description',
        'icon',
        'sort_order',
        'status'
    ];

    public function getPublished($limit = null) {
        return $this->where(['status' => 'published'], 'sort_order ASC', $limit);
    }
}
