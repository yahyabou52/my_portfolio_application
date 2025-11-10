<?php

require_once ROOT_PATH . '/app/core/BaseModel.php';

class HeroSection extends BaseModel {
    protected $table = 'hero_section';
    protected $fillable = [
        'hero_intro_prefix',
        'hero_intro_name_first',
        'hero_intro_name_rest',
        'hero_intro_suffix',
        'hero_title',
        'hero_subtitle',
        'hero_description',
        'hero_primary_cta_text',
        'hero_primary_cta_url',
        'hero_secondary_cta_text',
        'hero_secondary_cta_url',
        'hero_background_image_path',
        'hero_background_image_alt',
        'scroll_indicator_text'
    ];

    public function getActive(): ?array {
        return $this->find(1);
    }

    public function updateActive(array $data): bool {
        $record = $this->getActive();
        if ($record) {
            return $this->update($record['id'], $data);
        }
        $data['id'] = 1;
        return (bool)$this->create($data);
    }
}
