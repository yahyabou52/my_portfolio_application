<?php
require_once __DIR__ . '/../config/config.php';
$pdo = new PDO('mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';charset=utf8mb4', DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
if (!preg_match('/^[a-zA-Z0-9_]+$/', PLATFORM_DB)) { throw new RuntimeException('Invalid platform database name'); }
$pdo->exec('CREATE DATABASE IF NOT EXISTS ' . PLATFORM_DB . ' CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
$db = TenantContext::registry();
$db->exec("CREATE TABLE IF NOT EXISTS sites (id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, slug VARCHAR(40) NOT NULL UNIQUE, name VARCHAR(150) NOT NULL, owner_email VARCHAR(190) NOT NULL, database_name VARCHAR(64) NOT NULL UNIQUE, status VARCHAR(20) NOT NULL DEFAULT 'provisioning', created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)");
// Bootstrap the demo only when its database has no tables. Never reset an existing site.
$pdo->exec('CREATE DATABASE IF NOT EXISTS portfolio_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
$demo = new PDO('mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=portfolio_db;charset=utf8mb4', DB_USER, DB_PASS, [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
$count = $demo->query("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='portfolio_db'")->fetchColumn();
if ((int)$count === 0) {
    $demo->exec(file_get_contents(ROOT_PATH . '/database/schema.sql'));
    $demo->exec(file_get_contents(ROOT_PATH . '/database/sample_data.sql'));
}
require_once ROOT_PATH . '/scripts/update_schema.php';
migratePortfolioSchema($demo);
$q = $db->prepare('INSERT IGNORE INTO sites (slug, name, owner_email, database_name, status) VALUES (?, ?, ?, ?, ?)');
$q->execute(['demo', 'Demo portfolio', 'admin@portfolio.com', 'portfolio_db', 'active']);
echo "Platform ready. Existing website preserved at /s/demo
";
