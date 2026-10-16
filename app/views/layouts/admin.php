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
    <meta name="csrf-token" content="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
    <script src="<?= asset('js/site-session.js') ?>"></script>
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
    <link rel="icon" type="image/x-icon" href="<?= asset('images/placeholder.svg') ?>">
<link rel="stylesheet" href="<?= asset('css/studio.css') ?>">
</head>
<body class="admin-body <?= ($page ?? '') === 'admin-login' ? 'login-page' : '' ?>">
<?php $e=fn($v)=>htmlspecialchars((string)$v,ENT_QUOTES,'UTF-8'); if(($page??'')!=='admin-login'): $section=explode('/',explode('/admin/', $requestedPath)[1]??'dashboard')[0]; $identityPage=$section==='website'&&($_GET['group']??'')==='identity'; ?>
<header class="studio-header"><button id="sidebarToggle" class="btn btn-outline-secondary" aria-label="Toggle navigation" aria-controls="modernSidebar" aria-expanded="false"><i class="bi bi-list"></i></button><a class="studio-brand" href="<?= url('admin/dashboard') ?>">Website studio</a><div class="ms-auto d-flex gap-2"><a class="btn btn-sm btn-outline-primary" target="_blank" rel="noopener" href="<?= url() ?>">Open website <i class="bi bi-box-arrow-up-right"></i></a><div class="dropdown"><button class="btn btn-sm btn-light dropdown-toggle" data-bs-toggle="dropdown">Account</button><ul class="dropdown-menu dropdown-menu-end"><li><a class="dropdown-item" href="<?= url('admin/users') ?>">Manage accounts</a></li><li><a class="dropdown-item" href="<?= url('admin/logout') ?>">Sign out</a></li></ul></div></div></header>
<button id="sidebarOverlay" class="studio-overlay" aria-label="Close navigation" hidden></button><aside id="modernSidebar" class="studio-sidebar"><p class="small text-uppercase text-muted px-3">Manage your website</p><nav aria-label="Dashboard navigation">
<?php $nav=[['dashboard','Overview','grid'],['website','Website pages','window'],['website?group=identity','Brand & contact','person-badge'],['projects','Projects','collection'],['services','Services','briefcase'],['media','Media library','images'],['messages','Messages','inbox'],['theme','Appearance','palette']]; foreach($nav as [$path,$label,$icon]): $active=$path==='website?group=identity'?$identityPage:($path==='website'?(!$identityPage&&in_array($section,['website','hero','about','timeline','skills','testimonials','featured-projects','home','navigation','footer','pages'])):$section===$path); ?>
<a class="studio-nav <?= $active?'active':'' ?>" <?= $active?'aria-current="page"':'' ?> href="<?= $e(url('admin/'.$path)) ?>"><i class="bi bi-<?= $icon ?>"></i><?= $label ?></a><?php endforeach; ?></nav><div class="studio-sidebar-note">Edit your content here.<br>Save to update your website.</div></aside>
<main id="mainContent" class="studio-main">
<?php foreach($_SESSION['flash']??[] as $type=>$message): if(!is_string($message))continue; $kind=in_array($type,['success','warning','info'])?$type:'danger'; ?><div class="alert alert-<?= $kind ?> alert-dismissible show" role="alert"><?= $e($message) ?><button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Dismiss"></button></div><?php unset($_SESSION['flash'][$type]); endforeach; ?>
<?php $context=['hero'=>'home','featured-projects'=>'home','home'=>'home','about'=>'about','timeline'=>'about','skills'=>'about','testimonials'=>'home','services'=>'services','projects'=>'portfolio','navigation'=>'navigation']; if(isset($context[$section])): ?><a class="d-inline-block mb-3 small" href="<?= url('admin/website?group='.$context[$section]) ?>"><i class="bi bi-arrow-left"></i> Back to <?= ucfirst($context[$section]) ?> page editor</a><?php endif; ?>
<?= $content ?></main>
<?php else: ?><main class="container-fluid min-vh-100 d-flex align-items-center justify-content-center"><?= $content ?></main><?php endif; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script><script src="<?= asset('js/admin.js') ?>"></script><script src="<?= asset('js/admin-modern.js') ?>"></script></body></html>