<?php

require_once ROOT_PATH . '/app/core/BaseModel.php';

class PricingPackageFeature extends BaseModel {
    protected $table = 'pricing_package_features';
    protected $fillable = [
        'package_id',
        'feature_text',
        'sort_order'
    ];

    public function getByPackage($packageId) {
        return $this->where(['package_id' => $packageId], 'sort_order ASC');
    }

    public function deleteByPackage($packageId) {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE package_id = :package_id");
        $stmt->bindValue(':package_id', $packageId, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
