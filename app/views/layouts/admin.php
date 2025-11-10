<?php
$currentPageSlug = $page ?? '';
$requestedUri = $_SERVER['REQUEST_URI'] ?? '';
$requestedPath = strtok($requestedUri, '?') ?: '';

if (!function_exists('admin_nav_path_contains')) {
    function admin_nav_path_contains(string $path, string $needle): bool {
        return strpos($path, $needle) !== false;
    }
}
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Admin Panel' ?></title>
    
    <!-- Content Security Policy for admin area -->
    <meta http-equiv="Content-Security-Policy" content="default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net https://fonts.googleapis.com; style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://fonts.googleapis.com https://fonts.gstatic.com; font-src 'self' https://fonts.gstatic.com https://cdn.jsdelivr.net; img-src 'self' data: https:; connect-src 'self' https:;"><?php
    // Also set CSP header
    if (!headers_sent()) {
        header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net https://fonts.googleapis.com; style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://fonts.googleapis.com https://fonts.gstatic.com; font-src 'self' https://fonts.gstatic.com https://cdn.jsdelivr.net; img-src 'self' data: https:; connect-src 'self' https:;");
    }
    ?>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= asset('css/style.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/admin.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/admin-modern.css') ?>">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?= asset('images/favicon.ico') ?>">
</head>
<body class="admin-body <?= ($page ?? '') === 'admin-login' ? 'login-page' : '' ?>">
    <?php if (($page ?? '') !== 'admin-login'): ?>
        <!-- Modern Top Navigation -->
        <nav class="navbar navbar-expand-lg modern-top-nav">
            <div class="container-fluid">
                <button class="sidebar-toggle me-3" type="button" id="sidebarToggle" aria-label="Toggle sidebar">
                    <i class="bi bi-list"></i>
                </button>
                
                <div class="d-flex align-items-center flex-grow-1">
                    <div class="d-none d-lg-block">
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="<?= url('admin/dashboard') ?>" class="text-decoration-none">Admin</a></li>
                                <li class="breadcrumb-item active text-muted"><?= ucfirst($page ?? 'Dashboard') ?></li>
                            </ol>
                        </nav>
                    </div>
                    <div class="d-lg-none">
                        <h6 class="mb-0 fw-semibold"><?= ucfirst($page ?? 'Dashboard') ?></h6>
                    </div>
                </div>
                
                <div class="navbar-nav">
                    <div class="nav-item dropdown">
                        <?php 
                        $userModel = new User();
                        $currentUser = $userModel->getCurrentUser();
                        $currentUserInitial = strtoupper(substr($currentUser['username'] ?? 'A', 0, 1));
                        $currentUserId = $currentUser['id'] ?? null;
                        ?>
                        <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="adminDropdown" role="button" 
                           data-bs-toggle="dropdown" aria-expanded="false">
                            <div class="user-avatar me-2">
                                <?= $currentUserInitial ?>
                            </div>
                            <span class="fw-medium text-dark d-none d-sm-inline"><?= htmlspecialchars($currentUser['username'] ?? 'Admin') ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="<?= url() ?>" target="_blank">
                                <i class="bi bi-box-arrow-up-right me-2"></i>View Site
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="<?= url('admin/logout') ?>">
                                <i class="bi bi-box-arrow-right me-2"></i>Logout
                            </a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Sidebar Overlay -->
        <div class="sidebar-overlay" id="sidebarOverlay"></div>

        <!-- Modern Sidebar -->
        <div class="modern-sidebar" id="modernSidebar">
            <div class="sidebar-header">
                <a href="<?= url('admin/dashboard') ?>" class="sidebar-brand">
                    <i class="bi bi-layers"></i>
                    <span>Portfolio Admin</span>
                </a>
            </div>
            
            <div class="sidebar-content">
                <nav class="navbar-nav flex-column">
                    <a class="nav-link <?= $currentPageSlug === 'admin-dashboard' ? 'active' : '' ?>" href="<?= url('admin/dashboard') ?>">
                        <i class="bi bi-house"></i>
                        <span>Dashboard</span>
                    </a>

                    <div class="nav-section">
                        <h6 class="nav-section-title">Home Page</h6>
                        <a class="nav-link nav-sublink <?= strpos($currentPageSlug, 'hero') !== false ? 'active' : '' ?>" href="<?= url('admin/hero') ?>">
                            <i class="bi bi-lightning-charge"></i>
                            <span>Hero Section</span>
                        </a>
                        <a class="nav-link nav-sublink <?= strpos($currentPageSlug, 'featured-projects') !== false ? 'active' : '' ?>" href="<?= url('admin/featured-projects') ?>">
                            <i class="bi bi-award"></i>
                            <span>Featured Work</span>
                        </a>
                        <a class="nav-link nav-sublink <?= strpos($currentPageSlug, 'services-preview') !== false ? 'active' : '' ?>" href="<?= url('admin/services/preview') ?>">
                            <i class="bi bi-grid"></i>
                            <span>Services Preview</span>
                        </a>
                        <a class="nav-link nav-sublink <?= strpos($currentPageSlug, 'skills') !== false ? 'active' : '' ?>" href="<?= url('admin/skills') ?>">
                            <i class="bi bi-tools"></i>
                            <span>Skills &amp; Tools Preview</span>
                        </a>
                        <a class="nav-link nav-sublink <?= strpos($currentPageSlug, 'testimonials') !== false ? 'active' : '' ?>" href="<?= url('admin/testimonials') ?>">
                            <i class="bi bi-chat-square-quote"></i>
                            <span>Testimonials Preview</span>
                        </a>
                        <a class="nav-link nav-sublink <?= strpos($currentPageSlug, 'home-cta') !== false ? 'active' : '' ?>" href="<?= url('admin/home/page-cta') ?>">
                            <i class="bi bi-megaphone"></i>
                            <span>Homepage CTA</span>
                        </a>
                    </div>

                    <div class="nav-section">
                        <h6 class="nav-section-title">About Page</h6>
                        <a class="nav-link nav-sublink <?= strpos($currentPageSlug, 'about') !== false || admin_nav_path_contains($requestedPath, '/admin/pages/about') ? 'active' : '' ?>" href="<?= url('admin/about') ?>">
                            <i class="bi bi-person-lines-fill"></i>
                            <span>About Content</span>
                        </a>
                        <a class="nav-link nav-sublink <?= strpos($currentPageSlug, 'timeline') !== false ? 'active' : '' ?>" href="<?= url('admin/timeline') ?>">
                            <i class="bi bi-clock-history"></i>
                            <span>Timeline</span>
                        </a>
                    </div>

                    <div class="nav-section">
                        <h6 class="nav-section-title">Services Page</h6>
                        <a class="nav-link nav-sublink <?= $currentPageSlug === 'admin-services' ? 'active' : '' ?>" href="<?= url('admin/services') ?>">
                            <i class="bi bi-collection"></i>
                            <span>Services List</span>
                        </a>
                        <a class="nav-link nav-sublink <?= $currentPageSlug === 'admin-service-features' ? 'active' : '' ?>" href="<?= url('admin/services/features') ?>">
                            <i class="bi bi-stars"></i>
                            <span>Service Features</span>
                        </a>
                        <a class="nav-link nav-sublink <?= admin_nav_path_contains($requestedPath, '/admin/pages/services') ? 'active' : '' ?>" href="<?= url('admin/pages/services/edit?section=process') ?>">
                            <i class="bi bi-diagram-3"></i>
                            <span>Design Process Steps</span>
                        </a>
                        <a class="nav-link nav-sublink <?= admin_nav_path_contains($requestedPath, '/admin/pages/services') ? 'active' : '' ?>" href="<?= url('admin/pages/services/edit?section=pricing') ?>">
                            <i class="bi bi-currency-dollar"></i>
                            <span>Pricing Plans</span>
                        </a>
                        <a class="nav-link nav-sublink <?= admin_nav_path_contains($requestedPath, '/admin/pages/services') ? 'active' : '' ?>" href="<?= url('admin/pages/services/edit?section=faq') ?>">
                            <i class="bi bi-question-circle"></i>
                            <span>FAQ Items</span>
                        </a>
                    </div>

                    <div class="nav-section">
                        <h6 class="nav-section-title">Portfolio</h6>
                        <a class="nav-link nav-sublink <?= strpos($currentPageSlug, 'projects') !== false ? 'active' : '' ?>" href="<?= url('admin/projects') ?>">
                            <i class="bi bi-briefcase"></i>
                            <span>Projects</span>
                        </a>
                        <a class="nav-link nav-sublink <?= strpos($currentPageSlug, 'projects') !== false ? 'active' : '' ?>" href="<?= url('admin/projects?view=gallery') ?>">
                            <i class="bi bi-images"></i>
                            <span>Project Gallery</span>
                        </a>
                        <a class="nav-link nav-sublink <?= admin_nav_path_contains($requestedPath, '/admin/pages/portfolio') ? 'active' : '' ?>" href="<?= url('admin/pages/portfolio/edit?section=categories') ?>">
                            <i class="bi bi-tags"></i>
                            <span>Categories</span>
                        </a>
                    </div>

                    <div class="nav-section">
                        <h6 class="nav-section-title">Testimonials</h6>
                        <a class="nav-link nav-sublink <?= strpos($currentPageSlug, 'testimonials') !== false ? 'active' : '' ?>" href="<?= url('admin/testimonials') ?>">
                            <i class="bi bi-chat-dots"></i>
                            <span>Manage Testimonials</span>
                        </a>
                    </div>

                    <div class="nav-section">
                        <h6 class="nav-section-title">Contact Page</h6>
                        <a class="nav-link nav-sublink <?= admin_nav_path_contains($requestedPath, '/admin/pages/contact') ? 'active' : '' ?>" href="<?= url('admin/pages/contact/edit?section=info') ?>">
                            <i class="bi bi-telephone"></i>
                            <span>Contact Information</span>
                        </a>
                        <a class="nav-link nav-sublink <?= admin_nav_path_contains($requestedPath, '/admin/pages/contact') ? 'active' : '' ?>" href="<?= url('admin/pages/contact/edit?section=social') ?>">
                            <i class="bi bi-share"></i>
                            <span>Social Links</span>
                        </a>
                        <a class="nav-link nav-sublink <?= strpos($currentPageSlug, 'messages') !== false ? 'active' : '' ?>" href="<?= url('admin/messages') ?>">
                            <i class="bi bi-envelope-open"></i>
                            <span>View Messages</span>
                            <?php if (isset($unread_messages) && $unread_messages > 0): ?>
                                <span class="badge bg-danger rounded-pill"><?= $unread_messages ?></span>
                            <?php endif; ?>
                        </a>
                    </div>

                    <div class="nav-section">
                        <h6 class="nav-section-title">Footer &amp; Global Settings</h6>
                        <a class="nav-link nav-sublink <?= strpos($currentPageSlug, 'footer') !== false ? 'active' : '' ?>" href="<?= url('admin/footer') ?>">
                            <i class="bi bi-layout-text-window"></i>
                            <span>Footer Content</span>
                        </a>
                        <a class="nav-link nav-sublink <?= strpos($currentPageSlug, 'navigation') !== false ? 'active' : '' ?>" href="<?= url('admin/navigation') ?>">
                            <i class="bi bi-list-ul"></i>
                            <span>Navigation Menu</span>
                        </a>
                        <a class="nav-link nav-sublink <?= strpos($currentPageSlug, 'theme') !== false || strpos($currentPageSlug, 'settings') !== false ? 'active' : '' ?>" href="<?= url('admin/theme') ?>">
                            <i class="bi bi-palette"></i>
                            <span>Site Identity (logo, favicon, colors, theme)</span>
                        </a>
                    </div>

                    <div class="nav-section">
                        <h6 class="nav-section-title">Admin Settings</h6>
                        <a class="nav-link nav-sublink <?= strpos($currentPageSlug, 'users') !== false ? 'active' : '' ?>" href="<?= url('admin/users') ?>">
                            <i class="bi bi-people"></i>
                            <span>Admin Accounts</span>
                        </a>
                        <?php $changePasswordLink = $currentUserId ? url('admin/users/' . $currentUserId . '/edit') : url('admin/users'); ?>
                        <a class="nav-link nav-sublink <?= admin_nav_path_contains($requestedPath, '/admin/users/' . ($currentUserId ?? '')) && strpos($requestedPath, '/edit') !== false ? 'active' : '' ?>" href="<?= $changePasswordLink ?>">
                            <i class="bi bi-lock"></i>
                            <span>Change Password</span>
                        </a>
                    </div>
                </nav>
            </div>
        </div>

        <!-- Main Content -->
        <div class="main-content" id="mainContent">
            <!-- Flash Messages -->
            <?php 
            session_start_safe();
            $flash = $_SESSION['flash'] ?? [];
            foreach ($flash as $type => $message): 
                if ($type === 'form_errors' || $type === 'form_data') continue;
                $alertClass = $type === 'error' ? 'danger' : $type;
            ?>
                <div class="alert alert-<?= $alertClass ?> alert-dismissible fade show position-fixed" 
                     style="top: 80px; right: 20px; z-index: 1050; max-width: 400px;" role="alert">
                    <?= htmlspecialchars($message) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php 
            unset($_SESSION['flash'][$type]);
            endforeach; 
            ?>

            <?= $content ?>
        </div>
    <?php else: ?>
        <!-- Login Page -->
        <div class="container-fluid vh-100 d-flex align-items-center justify-content-center">
            <?= $content ?>
        </div>
    <?php endif; ?>
    
    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JavaScript -->
    <script src="<?= asset('js/admin.js') ?>"></script>
    <script src="<?= asset('js/admin-modern.js') ?>"></script>
</body>
</html>