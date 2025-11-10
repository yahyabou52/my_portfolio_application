<?php

require_once ROOT_PATH . '/app/core/BaseModel.php';

class SkillItem extends BaseModel {
    protected $table = 'skills_items';
    protected $fillable = [
        'category_id',
        'skill_name',
        'proficiency_level',
        'sort_order',
        'is_featured'
    ];

    public function getByCategory($categoryId) {
        return $this->where(['category_id' => $categoryId], 'sort_order ASC, skill_name ASC');
    }
}
