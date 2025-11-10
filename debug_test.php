<?php
// Debug script to test admin functionality
require_once __DIR__ . '/config/config.php';
require_once ROOT_PATH . '/app/models/User.php';
require_once ROOT_PATH . '/app/models/Page.php';

try {
    echo "Testing database connection...\n";
    $db = Database::getInstance()->getConnection();
    echo "Database connection: OK\n";
    
    echo "Testing User model...\n";
    $userModel = new User();
    $users = $userModel->all();
    echo "User model all() method: OK - Found " . count($users) . " users\n";
    
    echo "Testing Page model...\n";
    $pageModel = new Page();
    $pages = $pageModel->all();
    echo "Page model all() method: OK - Found " . count($pages) . " pages\n";
    
    echo "Testing admin user authentication...\n";
    $admin = $userModel->findBy('username', 'admin');
    if ($admin) {
        echo "Admin user found: " . $admin['username'] . " (" . $admin['email'] . ")\n";
    } else {
        echo "Admin user NOT found\n";
    }
    
    echo "\nAll tests passed!\n";
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}