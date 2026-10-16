<?php
final class WebsiteContent {
    private static ?array $values = null;
    public static function manifest(): array { return json_decode(file_get_contents(ROOT_PATH . '/config/website-fields.json'), true, 512, JSON_THROW_ON_ERROR); }
    public static function values(): array {
        if (self::$values === null) {
            self::$values = [];
            $rows = Database::getInstance()->getConnection()->query('SELECT setting_key, setting_value FROM site_settings')->fetchAll();
            foreach ($rows as $row) { self::$values[$row['setting_key']] = $row['setting_value']; }
        }
        return self::$values;
    }
    public static function get(string $key, string $default = ''): string { return (string)(self::values()['content.' . $key] ?? $default); }
    public static function visible(string $key): bool { return self::get($key, '1') !== '0'; }
    public static function orderSections(string $html): string {
        preg_match_all('/<section\b[^>]*>|<\/section>/i', $html, $tags, PREG_OFFSET_CAPTURE);
        $depth=0; $start=0; $blocks=[]; $key='';
        foreach ($tags[0] as [$tag,$offset]) {
            if (str_starts_with(strtolower($tag), '</')) {
                $depth--;
                if ($depth === 0 && $key !== '') { $blocks[]=['start'=>$start,'length'=>$offset+strlen($tag)-$start,'html'=>substr($html,$start,$offset+strlen($tag)-$start),'order'=>(int)self::get($key . '.order', (string)(count($blocks)*10))]; }
            } else {
                if ($depth === 0) { $start=$offset; preg_match('/data-section-key="([^"]+)"/', $tag,$match); $key=$match[1]??''; }
                $depth++;
            }
        }
        if ($depth !== 0 || !$blocks) { return $html; }
        $sorted=$blocks; usort($sorted,fn($a,$b)=>$a['order']<=>$b['order']);
        for($i=count($blocks)-1;$i>=0;$i--){$html=substr_replace($html,$sorted[$i]['html'],$blocks[$i]['start'],$blocks[$i]['length']);}
        return $html;
    }
    public static function save(array $values): void {
        $db = Database::getInstance()->getConnection();
        $q = $db->prepare("INSERT INTO site_settings (setting_key, setting_value, setting_type, setting_group) VALUES (?, ?, 'textarea', 'website') ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value), updated_at=NOW()");
        foreach ($values as $key => $value) { $q->execute(['content.' . $key, $value]); }
        self::$values = null;
    }
}
function site_text(string $key, string $default = ''): string { return htmlspecialchars(WebsiteContent::get($key, html_entity_decode($default, ENT_QUOTES, 'UTF-8')), ENT_QUOTES, 'UTF-8'); }
function csrf_token(): string { session_start_safe(); return $_SESSION['csrf_token'] ??= bin2hex(random_bytes(32)); }
function verify_csrf(): void { if (!hash_equals(csrf_token(), (string)($_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? ''))) { http_response_code(403); exit('Your session expired. Reload the page and try again.'); } }
