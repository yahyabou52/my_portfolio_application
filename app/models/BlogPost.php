<?php

require_once ROOT_PATH . '/app/core/BaseModel.php';

class BlogPost extends BaseModel {
    protected $table = 'blog_posts';
    protected $fillable = [
        'title',
        'slug',
        'excerpt',
        'content',
        'featured_image',
        'meta_description',
        'meta_keywords',
        'author_id',
        'category',
        'tags',
        'is_published',
        'is_featured',
        'published_at'
    ];
    
    public function getPublished($limit = null) {
        return $this->where(['is_published' => 1], 'published_at DESC', $limit);
    }
    
    public function getFeatured($limit = null) {
        return $this->where(['is_published' => 1, 'is_featured' => 1], 'published_at DESC', $limit);
    }
    
    public function getByCategory($category, $limit = null) {
        return $this->where(['category' => $category, 'is_published' => 1], 'published_at DESC', $limit);
    }
    
    public function getBySlug($slug) {
        $post = $this->findBy('slug', $slug);
        if ($post && $post['is_published']) {
            // Increment view count
            $this->incrementViews($post['id']);
            return $post;
        }
        return null;
    }
    
    public function incrementViews($id) {
        $stmt = $this->db->prepare("UPDATE {$this->table} SET views_count = views_count + 1 WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
    
    public function getCategories() {
        $stmt = $this->db->prepare("SELECT DISTINCT category FROM {$this->table} WHERE is_published = 1 AND category IS NOT NULL ORDER BY category");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    
    public function publish($id) {
        return $this->update($id, [
            'is_published' => 1,
            'published_at' => date('Y-m-d H:i:s')
        ]);
    }
    
    public function unpublish($id) {
        return $this->update($id, ['is_published' => 0]);
    }
    
    public function getStats() {
        $stmt = $this->db->prepare("
            SELECT 
                COUNT(*) as total,
                COUNT(CASE WHEN is_published = 1 THEN 1 END) as published,
                COUNT(CASE WHEN is_featured = 1 THEN 1 END) as featured,
                SUM(views_count) as total_views
            FROM {$this->table}
        ");
        $stmt->execute();
        return $stmt->fetch();
    }
    
    public function generateSlug($title) {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
        $slug = trim($slug, '-');
        
        // Check if slug exists and make it unique
        $originalSlug = $slug;
        $counter = 1;
        
        while ($this->findBy('slug', $slug)) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }
        
        return $slug;
    }
}