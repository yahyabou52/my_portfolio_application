<?php

require_once ROOT_PATH . '/app/core/BaseModel.php';

class SiteSetting extends BaseModel {
    protected $table = 'settings_site';
    protected $fillable = [
        'site_title',
        'site_tagline',
        'site_description',
        'nav_cta_text',
        'nav_cta_url',
        'footer_text',
        'logo_path',
        'favicon_path',
        'theme_default',
        'contact_email',
        'contact_phone',
        'contact_address',
        'social_links',
        'footer_links'
    ];

    public function getSettings(): ?array {
        return $this->find(1);
    }

    public function updateSettings(array $data): bool {
        $record = $this->getSettings();
        if ($record) {
            return $this->update($record['id'], $data);
        }
        $data['id'] = 1;
        return (bool)$this->create($data);
    }

    public function getSocialLinks(): array {
        $settings = $this->getSettings();
        if (!$settings || empty($settings['social_links'])) {
            return [];
        }

        $decoded = json_decode($settings['social_links'], true);
        return json_last_error() === JSON_ERROR_NONE && is_array($decoded) ? $decoded : [];
    }

    public function getFooterLinks(): array {
        $settings = $this->getSettings();
        if (!$settings || empty($settings['footer_links'])) {
            return [];
        }

        $decoded = json_decode($settings['footer_links'], true);
        return json_last_error() === JSON_ERROR_NONE && is_array($decoded) ? $decoded : [];
    }
}
