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
        $relativeDir = 'assets/uploads/sites/' . TenantContext::id() . '/';
        $uploadDir = ROOT_PATH . '/public/' . $relativeDir;
        
        // Create upload directory if it doesn't exist
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        if (($fileData['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK || !is_uploaded_file($fileData['tmp_name'])) { throw new Exception('Choose a file to upload.'); }
        if (filesize($fileData['tmp_name']) > 5 * 1024 * 1024) { throw new Exception('File size exceeds 5 MB.'); }
        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($fileData['tmp_name']);
        $types = ['image/jpeg'=>'jpg', 'image/png'=>'png', 'image/gif'=>'gif', 'image/webp'=>'webp', 'application/pdf'=>'pdf'];
        if (!isset($types[$mime])) { throw new Exception('Upload a JPG, PNG, GIF, WebP image or PDF document.'); }
        $fileData['type'] = $mime;
        $filename = bin2hex(random_bytes(16)) . '.' . $types[$mime];
        $filePath = $uploadDir . $filename;
        
        if (!move_uploaded_file($fileData['tmp_name'], $filePath)) {
            throw new Exception('Failed to upload file');
        }
        
        $fileType = strpos($fileData['type'], 'image/') === 0 ? 'image' : 'document';
        
        $data = [
            'filename' => $filename,
            'original_name' => $fileData['name'],
            'file_path' => $relativeDir . $filename,
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
        if (str_starts_with($file['file_path'], 'assets/uploads/sites/' . TenantContext::id() . '/') && !str_contains($file['file_path'], '..') && file_exists($fullPath)) {
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