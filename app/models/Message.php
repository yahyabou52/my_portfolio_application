<?php

require_once ROOT_PATH . '/app/core/BaseModel.php';

class Message extends BaseModel {
    protected $table = 'messages';
    protected $fillable = [
        'name',
        'email',
        'subject',
        'message',
        'is_read',
        'responded_at'
    ];
    
    public function getUnreadCount() {
        return $this->count(['is_read' => 0]);
    }
    
    public function markAsRead($id) {
        return $this->update($id, [
            'is_read' => 1
        ]);
    }
    
    public function markAsUnread($id) {
        return $this->update($id, [
            'is_read' => 0
        ]);
    }
    
    public function getRecent($limit = 5) {
        return $this->all('created_at DESC', $limit);
    }
    
    public function getUnread($limit = null) {
        return $this->where(['is_read' => 0], 'created_at DESC', $limit);
    }
    
    public function search($query, $limit = null) {
        $sql = "SELECT * FROM {$this->table} 
                WHERE name LIKE :query 
                   OR email LIKE :query 
                   OR subject LIKE :query 
                   OR message LIKE :query 
                ORDER BY created_at DESC";
        
        if ($limit) {
            $sql .= " LIMIT {$limit}";
        }
        
        $stmt = $this->db->prepare($sql);
        $searchTerm = "%{$query}%";
        $stmt->bindParam(':query', $searchTerm);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    public function getByDateRange($startDate, $endDate) {
        $sql = "SELECT * FROM {$this->table} 
                WHERE DATE(created_at) BETWEEN :start_date AND :end_date 
                ORDER BY created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':start_date', $startDate);
        $stmt->bindParam(':end_date', $endDate);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    public function validate($data) {
        $errors = [];
        
        if (empty($data['name']) || strlen(trim($data['name'])) < 2) {
            $errors['name'] = 'Name must be at least 2 characters long';
        }
        
        if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Please provide a valid email address';
        }
        
        if (empty($data['subject']) || strlen(trim($data['subject'])) < 5) {
            $errors['subject'] = 'Subject must be at least 5 characters long';
        }
        
        if (empty($data['message']) || strlen(trim($data['message'])) < 10) {
            $errors['message'] = 'Message must be at least 10 characters long';
        }
        
        return $errors;
    }
}