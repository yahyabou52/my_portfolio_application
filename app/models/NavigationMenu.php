<?php

require_once ROOT_PATH . '/app/core/BaseModel.php';

class NavigationMenu extends BaseModel {
    protected $table = 'navigation_menu';
    protected $fillable = [
        'title',
        'url',
        'icon',
        'parent_id',
        'sort_order',
        'is_active',
        'target'
    ];
    
    public function getMenuItems($parentId = null) {
        $sql = "SELECT * FROM {$this->table} WHERE parent_id " . 
               ($parentId ? "= ?" : "IS NULL") . 
               " ORDER BY sort_order ASC, title ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($parentId ? [$parentId] : []);
        return $stmt->fetchAll();
    }
    
    public function getActiveMenuItems($parentId = null) {
        $sql = "SELECT * FROM {$this->table} WHERE is_active = 1 AND parent_id " . 
               ($parentId ? "= ?" : "IS NULL") . 
               " ORDER BY sort_order ASC, title ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($parentId ? [$parentId] : []);
        return $stmt->fetchAll();
    }
    
    public function getFullMenuTree() {
        $parentItems = $this->getMenuItems();
        $menuTree = [];
        
        foreach ($parentItems as $parent) {
            $parent['children'] = $this->getMenuItems($parent['id']);
            $menuTree[] = $parent;
        }
        
        return $menuTree;
    }
    
    public function updateSortOrder($itemId, $sortOrder) {
        return $this->update($itemId, ['sort_order' => $sortOrder]);
    }
    
    public function getMaxSortOrder($parentId = null) {
        $sql = "SELECT MAX(sort_order) FROM {$this->table} WHERE parent_id " . 
               ($parentId ? "= ?" : "IS NULL");
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($parentId ? [$parentId] : []);
        return (int)$stmt->fetchColumn();
    }
}