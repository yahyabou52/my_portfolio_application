<?php
/** Resolves a site before any application model can open its database. */
final class TenantContext {
    private static ?array $site = null;
    public static function registry(): PDO {
        return new PDO('mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . PLATFORM_DB . ';charset=utf8mb4', DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, PDO::ATTR_EMULATE_PREPARES => false]);
    }
    public static function resolve(): void {
        if (PHP_SAPI === 'cli') { return; }
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $root = rtrim(parse_url(PLATFORM_URL, PHP_URL_PATH) ?: '', '/');
        if ($root !== '' && str_starts_with($path, $root . '/')) { $path = substr($path, strlen($root)); }
        if (!preg_match('#^/s/([a-z0-9][a-z0-9-]{2,39})(?:/|$)#', $path, $match)) { return; }
        $query = self::registry()->prepare('SELECT * FROM sites WHERE slug = ? AND status = ?');
        $query->execute([$match[1], 'active']);
        self::$site = $query->fetch() ?: null;
        if (!self::$site) { http_response_code(404); exit('Website not found.'); }
    }
    public static function editorMode(): string {
        if(!self::$site)return 'legacy';
        try {
            $mode=Database::getInstance()->getConnection()->query('SELECT editor_mode FROM builder_state WHERE id=1')->fetchColumn();
            return $mode==='website_first'?'website_first':'legacy';
        } catch(PDOException $e) { if($e->getCode()==='42S02')return 'legacy'; throw $e; }
    }
    public static function validSlug(string $slug): bool {
        return (bool)preg_match('/^[a-z0-9][a-z0-9-]{2,39}$/',$slug) && !in_array($slug,['admin','api','assets','signup','login','logout','account','settings','manage','edit','support','billing','www','privacy','terms','s'],true);
    }
    public static function site(): ?array { return self::$site; }
    public static function id(): int { return (int)(self::$site['id'] ?? 0); }
    public static function database(): string { return self::$site['database_name'] ?? (getenv('DB_NAME') ?: 'portfolio_db'); }
    public static function prefix(): string { return self::$site ? '/s/' . self::$site['slug'] : ''; }
}
