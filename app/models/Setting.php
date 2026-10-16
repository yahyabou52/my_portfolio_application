<?php

require_once ROOT_PATH . '/app/core/BaseModel.php';

class Setting extends BaseModel {
    protected $table = 'site_settings';
    public const SITE_FIELDS = ['site_title'=>'site_title','site_tagline'=>'site_tagline','site_description'=>'site_description','site_logo'=>'logo_path','favicon'=>'favicon_path','nav_cta_text'=>'nav_cta_text','nav_cta_url'=>'nav_cta_url','contact_email'=>'contact_email','contact_phone'=>'contact_phone','contact_address'=>'contact_address','footer_text'=>'footer_text','footer_links'=>'footer_links','social_links'=>'social_links','theme_default'=>'theme_default'];
    protected $fillable = [
        'setting_key',
        'setting_value',
        'setting_type',
        'setting_group',
        'is_editable'
    ];
    
    public function getByGroup($group) {
        $sql = "SELECT * FROM {$this->table} WHERE setting_group = ? ORDER BY setting_key ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$group]);
        $rows = $stmt->fetchAll();
        $site = $this->db->query('SELECT * FROM settings_site WHERE id=1')->fetch();
        foreach ($rows as &$row) { if (isset(self::SITE_FIELDS[$row['setting_key']])) { $row['setting_value'] = $site[self::SITE_FIELDS[$row['setting_key']]] ?? ''; } } unset($row);
        return $rows;
    }
    
    public function getByKey($key) {
        return $this->findBy('setting_key', $key);
    }
    
    public function updateByKey($key, $value) {
        if (isset(self::SITE_FIELDS[$key])) {
            $column = self::SITE_FIELDS[$key];
            $this->db->prepare("UPDATE settings_site SET {$column} = ?, updated_at=NOW() WHERE id=1")->execute([$value]);
        }
        $sql = "UPDATE {$this->table} SET setting_value = ?, updated_at = NOW() WHERE setting_key = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$value, $key]);

        if ($stmt->rowCount() === 0) {
            $existing = $this->findBy('setting_key', $key);
            if ($existing) {
                // Value unchanged; update considered successful
                return true;
            }

            $group = $this->inferGroupFromKey($key);
            $type = $this->inferSettingType($value);
            $insertSql = "INSERT INTO {$this->table} (setting_key, setting_value, setting_type, setting_group, created_at, updated_at) VALUES (?, ?, ?, ?, NOW(), NOW())";
            $insertStmt = $this->db->prepare($insertSql);
            return $insertStmt->execute([$key, $value, $type, $group]);
        }

        return true;
    }
    
    public function getAllGroups() {
        $sql = "SELECT DISTINCT setting_group FROM {$this->table} ORDER BY setting_group ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    
    public function getSettings($group = null) {
        if ($group) {
            return $this->getByGroup($group);
        }
        
        $sql = "SELECT * FROM {$this->table} ORDER BY setting_group, setting_key ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function updateSettings($settings) {
        $this->db->beginTransaction();
        
        try {
            foreach ($settings as $key => $value) {
                if (is_array($value)) {
                    $value = json_encode($value);
                }
                $this->updateByKey($key, $value);
            }
            
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollback();
            return false;
        }
    }

    private function inferGroupFromKey(string $key): string {
        $groups = [
            'hero_' => 'hero',
            'about_' => 'about',
            'footer_' => 'footer',
            'contact_' => 'contact',
            'social_' => 'social',
            'theme_' => 'theme',
            'nav_' => 'navigation',
            'page_' => 'pages',
        ];

        foreach ($groups as $prefix => $group) {
            if (strpos($key, $prefix) === 0) {
                return $group;
            }
        }

        return 'general';
    }

    private function inferSettingType($value): string {
        if (is_numeric($value)) {
            return 'number';
        }

        $normalized = strtolower(trim((string)$value));
        if (in_array($normalized, ['true', 'false', '1', '0'], true)) {
            return 'boolean';
        }

        if ($this->looksLikeJson($value)) {
            return 'json';
        }

        if (filter_var($value, FILTER_VALIDATE_URL)) {
            return 'url';
        }

        if (filter_var($value, FILTER_VALIDATE_EMAIL)) {
            return 'email';
        }

        return 'text';
    }

    private function looksLikeJson($value): bool {
        if (!is_string($value)) {
            return false;
        }

        $trimmed = trim($value);
        if ($trimmed === '') {
            return false;
        }

        $firstChar = $trimmed[0];
        $lastChar = substr($trimmed, -1);
        if (($firstChar !== '{' || $lastChar !== '}') && ($firstChar !== '[' || $lastChar !== ']')) {
            return false;
        }

        json_decode($trimmed);
        return json_last_error() === JSON_ERROR_NONE;
    }
}