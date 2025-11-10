<?php

require_once ROOT_PATH . '/app/core/BaseModel.php';

class Page extends BaseModel {
    protected $table = 'pages';
    protected $fillable = [
        'page_key',
        'title',
        'meta_description',
        'content',
        'sections',
        'is_active'
    ];
    
    public function getByKey($key) {
        return $this->findBy('page_key', $key);
    }
    
    public function updateByKey($key, $data) {
        $page = $this->getByKey($key);
        if (!$page) {
            return false;
        }
        
        return $this->update($page['id'], $data);
    }
    
    public function getActivePages() {
        return $this->findAll(['is_active' => 1]);
    }
}