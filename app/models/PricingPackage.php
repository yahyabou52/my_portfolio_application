<?php

require_once ROOT_PATH . '/app/core/BaseModel.php';

class PricingPackage extends BaseModel {
    protected $table = 'pricing_packages';
    protected $fillable = [
        'name',
        'price_label',
        'price_amount',
        'price_period',
        'description',
        'badge_text',
        'cta_text',
        'cta_url',
        'is_featured',
        'status',
        'sort_order'
    ];

    public function getPublished($limit = null) {
        return $this->where(['status' => 'published'], 'sort_order ASC, name ASC', $limit);
    }
}
