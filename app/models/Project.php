<?php

require_once ROOT_PATH . '/app/core/BaseModel.php';

class Project extends BaseModel {
    protected $table = 'projects';
    protected $fillable = [
        'title',
        'slug',
        'short_description',
        'description',
        'category',
        'technologies',
        'main_image_path',
        'main_image_alt',
        'client_name',
        'client_visibility',
        'project_url',
        'featured',
        'featured_sort_order',
        'status',
        'sort_order'
    ];
    
    public function findBySlug($slug) {
        return $this->findBy('slug', $slug);
    }
    
    public function getFeatured($limit = 3) {
        $order = 'featured_sort_order ASC, sort_order ASC, id ASC';
        return $this->where(['featured' => 1, 'status' => 'published'], $order, $limit);
    }
    
    public function getPublished($limit = null) {
        return $this->where(['status' => 'published'], 'sort_order ASC', $limit);
    }
    
    public function getByCategory($category, $limit = null) {
        return $this->where([
            'category' => $category, 
            'status' => 'published'
        ], 'sort_order ASC', $limit);
    }
    
    public function getCategories() {
        $sql = "SELECT DISTINCT category FROM {$this->table} 
                WHERE status = 'published' AND category IS NOT NULL 
                ORDER BY category ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        
        return array_column($stmt->fetchAll(), 'category');
    }
    
    public function search($query, $limit = null) {
    $sql = "SELECT * FROM {$this->table} 
        WHERE status = 'published' 
          AND (title LIKE :query 
               OR description LIKE :query 
               OR short_description LIKE :query 
               OR category LIKE :query 
               OR client_name LIKE :query)
                ORDER BY sort_order ASC";
        
        if ($limit) {
            $sql .= " LIMIT {$limit}";
        }
        
        $stmt = $this->db->prepare($sql);
        $searchTerm = "%{$query}%";
        $stmt->bindParam(':query', $searchTerm);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    public function getNext($currentId) {
        $sql = "SELECT * FROM {$this->table} 
                WHERE status = 'published' 
                  AND id > :current_id 
                ORDER BY id ASC 
                LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':current_id', $currentId, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetch();
    }
    
    public function getPrevious($currentId) {
        $sql = "SELECT * FROM {$this->table} 
                WHERE status = 'published' 
                  AND id < :current_id 
                ORDER BY id DESC 
                LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':current_id', $currentId, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetch();
    }
    
    public function getTechnologies($projectId) {
        $project = $this->find($projectId);
        if ($project && $project['technologies']) {
            $decoded = json_decode($project['technologies'], true);
            return json_last_error() === JSON_ERROR_NONE && is_array($decoded) ? $decoded : [];
        }
        return [];
    }
    
    public function countDistinctClients() {
        $sql = "SELECT COUNT(DISTINCT client_name) as total FROM {$this->table} WHERE status = 'published' AND client_name IS NOT NULL AND client_name <> ''";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch();
        return $result ? (int)$result['total'] : 0;
    }
    
    public function generateSlug($title, $ignoreId = null) {
        $slug = strtolower(trim($title));
        $slug = preg_replace('/[^a-z0-9-]/', '-', $slug);
        $slug = preg_replace('/-+/', '-', $slug);
        $slug = trim($slug, '-');
        
        if ($slug === '') {
            $slug = 'project';
        }
        
        // Check if slug exists and make it unique
        $originalSlug = $slug;
        $counter = 1;
        
        while ($existing = $this->findBySlug($slug)) {
            if ($ignoreId && isset($existing['id']) && (int)$existing['id'] === (int)$ignoreId) {
                break;
            }
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }
        
        return $slug;
    }
    
    public function validate($data) {
        $errors = [];
        
        if (empty($data['title']) || strlen(trim($data['title'])) < 3) {
            $errors['title'] = 'Title must be at least 3 characters long';
        }
        
        if (empty($data['short_description']) || strlen(trim($data['short_description'])) < 10) {
            $errors['short_description'] = 'Short description must be at least 10 characters long';
        }
        
        if (empty($data['description']) || strlen(trim($data['description'])) < 20) {
            $errors['description'] = 'Description must be at least 20 characters long';
        }
        
        if (empty($data['category'])) {
            $errors['category'] = 'Category is required';
        }

        if (empty($data['main_image_path'])) {
            $errors['main_image_path'] = 'Featured image path is required';
        }

        if (!in_array($data['status'] ?? 'draft', ['draft', 'published'], true)) {
            $errors['status'] = 'Status must be either published or draft';
        }

        if (!in_array($data['client_visibility'] ?? 'yes', ['yes', 'no'], true)) {
            $errors['client_visibility'] = 'Client visibility selection is invalid';
        }
        
        if (isset($data['project_url']) && !empty($data['project_url']) && !filter_var($data['project_url'], FILTER_VALIDATE_URL)) {
            $errors['project_url'] = 'Please provide a valid project URL';
        }

        return $errors;
    }
}