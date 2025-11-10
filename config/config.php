<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

/**
 * Database Configuration
 */
define('DB_HOST', 'localhost');
define('DB_NAME', 'portfolio_db');
define('DB_USER', 'root');
define('DB_PASS', '');

/**
 * Application Configuration
 */
define('BASE_URL', 'http://localhost/portfolio-web/public');
define('ROOT_PATH', __DIR__ . '/..');

/**
 * Database Connection
 */
class Database {
    private static $instance = null;
    private $connection;
    
    private function __construct() {
        try {
            $this->connection = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME,
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
    return url('assets/' . ltrim($path, '/'));
}

function media_url($path = '') {
    if (!$path) {
        return '';
    }

    if (preg_match('#^https?://#i', $path)) {
        return $path;
    }

    $normalized = ltrim($path, '/');

    if (strpos($normalized, 'assets/') === 0) {
        return url($normalized);
    }

    return asset($normalized);
}

function navbar_build_nav_url($url) {
    if (!$url) {
        return '#';
    }

    if (preg_match('#^(https?:)?//#i', $url) || preg_match('#^(mailto:|tel:)#i', $url) || strpos($url, '#') === 0) {
        return $url;
    }

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
        session_start();
    }
    return true;
}