<?php

require_once ROOT_PATH . '/app/core/BaseModel.php';

class Media extends BaseModel {
    protected $table = 'media_files';
    protected $fillable = [
        'filename',
        'original_name',
        'file_path',
        'file_type',
        'file_size',
        'mime_type',
        'alt_text',
        'title',
        'uploaded_by'
    ];
    protected $timestamps = false;
    
    public function getByType($type) {
        $sql = "SELECT * FROM {$this->table} WHERE file_type = ? ORDER BY created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$type]);
        return $stmt->fetchAll();
    }
    
    public function getImages() {
        return $this->getByType('image');
    }
    
    public function getDocuments() {
        return $this->getByType('document');
    }
    
    public function uploadFile($fileData, $uploadedBy = null) {
        $uploadDir = ROOT_PATH . '/public/assets/uploads/';
        
        // Create upload directory if it doesn't exist
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $allowedTypes = [
            'image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml',
            'application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
        ];
        
        if (!in_array($fileData['type'], $allowedTypes)) {
            throw new Exception('File type not allowed');
        }
        
        $maxSize = 5 * 1024 * 1024; // 5MB
        if ($fileData['size'] > $maxSize) {
            throw new Exception('File size too large');
        }
        
        $extension = pathinfo($fileData['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '_' . time() . '.' . $extension;
        $filePath = $uploadDir . $filename;
        
        if (!move_uploaded_file($fileData['tmp_name'], $filePath)) {
            throw new Exception('Failed to upload file');
        }
        
        $fileType = strpos($fileData['type'], 'image/') === 0 ? 'image' : 'document';
        
        $data = [
            'filename' => $filename,
            'original_name' => $fileData['name'],
            'file_path' => 'assets/uploads/' . $filename,
            'file_type' => $fileType,
            'file_size' => $fileData['size'],
            'mime_type' => $fileData['type'],
            'uploaded_by' => $uploadedBy
        ];
        
        return $this->create($data);
    }
    
    public function deleteFile($id) {
        $file = $this->find($id);
        if (!$file) {
            return false;
        }
        
        $fullPath = ROOT_PATH . '/public/' . $file['file_path'];
        if (file_exists($fullPath)) {
            unlink($fullPath);
        }
        
        return $this->delete($id);
    }
    
    public function updateFile($id, $data) {
        $allowedFields = ['alt_text', 'title'];
        $updateData = array_intersect_key($data, array_flip($allowedFields));
        
        return $this->update($id, $updateData);
    }
}