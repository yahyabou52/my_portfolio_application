<?php
// Emergency admin login bypass
require_once __DIR__ . '/../config/config.php';
require_once ROOT_PATH . '/app/models/User.php';

// Start session safely
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get admin user from database
$userModel = new User();
$admin = $userModel->findBy('username', 'admin');

if ($admin) {
    // Set session data
    $_SESSION['admin_user'] = $admin;
    $_SESSION['admin_logged_in'] = true;
    $_SESSION['admin_login_time'] = time();
    
    echo "✅ Successfully logged in as admin!<br>";
    echo "Now try accessing: <a href='" . url('admin/pages') . "'>Admin Pages</a><br>";
    echo "Or: <a href='" . url('admin/dashboard') . "'>Admin Dashboard</a><br>";
    
} else {
    echo "❌ Admin user not found in database";
}
?>