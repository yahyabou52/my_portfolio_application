<?php
// Simple test admin page to bypass potential issues
require_once __DIR__ . '/config/config.php';

// Start session safely
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Fake login for testing - REMOVE THIS AFTER TESTING
$_SESSION['admin_user'] = [
    'id' => 1,
    'username' => 'admin',
    'email' => 'admin@portfolio.com'
];
$_SESSION['admin_logged_in'] = true;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Test Page</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h2 class="mb-0">🎉 ADMIN SYSTEM TEST SUCCESS!</h2>
                    </div>
                    <div class="card-body">
                        <h4 class="text-success">✅ Everything is Working!</h4>
                        <p>The admin system is functional. Try these links:</p>
                        
                        <div class="list-group">
                            <a href="<?= url('admin/pages') ?>" class="list-group-item list-group-item-action">
                                📄 Pages Management
                            </a>
                            <a href="<?= url('admin/about') ?>" class="list-group-item list-group-item-action">
                                👤 About Management
                            </a>
                            <a href="<?= url('admin/services') ?>" class="list-group-item list-group-item-action">
                                ⚙️ Services Management
                            </a>
                            <a href="<?= url('admin/projects') ?>" class="list-group-item list-group-item-action">
                                💼 Projects Management
                            </a>
                            <a href="<?= url('admin/testimonials') ?>" class="list-group-item list-group-item-action">
                                ⭐ Testimonials Management
                            </a>
                            <a href="<?= url('admin/users') ?>" class="list-group-item list-group-item-action">
                                👥 Users Management
                            </a>
                        </div>
                        
                        <hr>
                        <p><strong>Session Status:</strong> <?= $_SESSION['admin_logged_in'] ? 'Logged In' : 'Not Logged In' ?></p>
                        <p><strong>User:</strong> <?= $_SESSION['admin_user']['username'] ?? 'None' ?></p>
                        
                        <div class="alert alert-warning">
                            <strong>Note:</strong> This is a test page with forced login. 
                            After testing, you should use the normal login: <br>
                            Username: <code>admin</code> | Password: <code>admin123</code>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>