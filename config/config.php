<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

/**
 * Database Configuration
 */
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_PORT', getenv('DB_PORT') ?: '3306');
define('PLATFORM_DB', getenv('PLATFORM_DB') ?: 'portfolio_platform');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');

/**
 * Application Configuration
 */
define('PLATFORM_URL', rtrim(getenv('BASE_URL') ?: 'http://localhost/portfolio-web/public', '/'));
define('ROOT_PATH', __DIR__ . '/..');
require_once ROOT_PATH . '/app/core/TenantContext.php';
TenantContext::resolve();
define('DB_NAME', TenantContext::database());
define('BASE_URL', PLATFORM_URL . TenantContext::prefix());

/**
 * Database Connection
 */
class Database {
    private static $instance = null;
    private $connection;
    
    private function __construct() {
        try {
            $this->connection = new PDO(
                "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch (PDOException $e) {
            die("Connection failed: " . $e->getMessage());
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->connection;
    }
}

/**
 * Helper Functions
 */
function url($path = '') {
    return BASE_URL . ($path ? '/' . ltrim($path, '/') : '');
}

function asset($path = '') {
    return PLATFORM_URL . '/assets/' . ltrim($path, '/');
}

function media_url($path = '') {
    if (!$path) { return ''; }
    $path = trim((string)$path);
    if (preg_match('#^https?://#i', $path)) { return $path; }
    $normalized = ltrim($path, '/');
    if (!str_starts_with($normalized, 'assets/')) { $normalized = 'assets/' . $normalized; }
    if (str_contains($normalized, '..') || !is_file(ROOT_PATH . '/public/' . $normalized)) {
        return asset('images/placeholder.svg');
    }
    return PLATFORM_URL . '/' . $normalized;
}

function navbar_build_nav_url($url) {
    if (!$url) {
        return '#';
    }

    if (preg_match('#^(https?:)?//#i', $url) || preg_match('#^(mailto:|tel:)#i', $url) || strpos($url, '#') === 0) {
        return $url;
    }

    if (preg_match('/^[a-z][a-z0-9+.-]*:/i', $url)) { return '#'; }
    return url($url);
}

function redirect($url) {
    header('Location: ' . (strpos($url, 'http') === 0 ? $url : url($url)));
    exit;
}

function session_start_safe() {
    if (session_status() === PHP_SESSION_NONE) {
        // Check if headers have been sent
        if (headers_sent($filename, $line)) {
            error_log("Headers already sent in $filename at line $line");
            return false;
        }
        session_name('portfolio_' . TenantContext::id());
        session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax', 'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off', 'path' => (parse_url(BASE_URL, PHP_URL_PATH) ?: '/')]);
        session_start();
    }
    return true;
}
require_once ROOT_PATH . '/app/core/WebsiteContent.php';
