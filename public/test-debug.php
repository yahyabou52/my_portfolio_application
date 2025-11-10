<?php
// Simple test to check what's causing the error
require_once __DIR__ . '/../config/config.php';

try {
    echo "1. Config loaded successfully<br>";
    
    require_once ROOT_PATH . '/app/models/User.php';
    echo "2. User model loaded<br>";
    
    $userModel = new User();
    echo "3. User model instantiated<br>";
    
    $userModel->requireAuth();
    echo "4. Auth check passed<br>";
    
} catch (Exception $e) {
    echo "<h1>Error Found!</h1>";
    echo "<p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>File:</strong> " . htmlspecialchars($e->getFile()) . "</p>";
    echo "<p><strong>Line:</strong> " . $e->getLine() . "</p>";
    echo "<pre><strong>Stack Trace:</strong>\n" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}
?>