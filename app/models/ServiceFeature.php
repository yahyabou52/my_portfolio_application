<?php

require_once ROOT_PATH . '/app/core/BaseModel.php';

class ServiceFeature extends BaseModel {
    protected $table = 'service_features';
    protected $fillable = [
        'service_id',
        'feature_text',
        'sort_order'
    ];

    public function getByService($serviceId) {
        return $this->where(['service_id' => $serviceId], 'sort_order ASC');
    }

    public function deleteByService($serviceId) {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE service_id = :service_id");
        $stmt->bindValue(':service_id', $serviceId, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
