<?php

require_once ROOT_PATH . '/app/core/BaseModel.php';

class FaqItem extends BaseModel {
    protected $table = 'faq_items';
    protected $fillable = [
        'page',
        'question',
        'answer',
        'sort_order',
        'is_active'
    ];

    public function getActiveByPage(string $page) {
        return $this->where(['page' => $page, 'is_active' => 1], 'sort_order ASC');
    }
}
