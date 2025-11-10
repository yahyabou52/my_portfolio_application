<?php
$siteSettings = $siteSettings ?? [];
$navigationMenu = $navigationMenu ?? [];
$brandTitle = $siteSettings['site_title'] ?? 'Yahya Bouhafs';
$brandTagline = $siteSettings['site_tagline'] ?? '';
$brandLogoSource = $siteSettings['site_logo'] ?? '';
$brandLogo = is_string($brandLogoSource) ? media_url($brandLogoSource) : '';
$brandLogoUrl = '';

if (is_string($brandLogo) && $brandLogo !== '') {
    $logoHost = parse_url($brandLogo, PHP_URL_HOST);
    $baseHost = parse_url(BASE_URL, PHP_URL_HOST);

    if ($logoHost && $baseHost && strcasecmp($logoHost, $baseHost) !== 0) {
        $brandLogoUrl = $brandLogo;
    } else {
        $logoPath = parse_url($brandLogo, PHP_URL_PATH) ?: '';
        $basePath = parse_url(BASE_URL, PHP_URL_PATH) ?: '';

        if ($logoPath !== '' && $basePath !== '' && strpos($logoPath, $basePath) === 0) {
            $logoPath = substr($logoPath, strlen($basePath)) ?: '';
        }

        $relativeLogoPath = ltrim($logoPath, '/');

        if ($relativeLogoPath !== '') {
            $filesystemPath = ROOT_PATH . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativeLogoPath);

            if (is_file($filesystemPath)) {
                $brandLogoUrl = $brandLogo;
            }
        }
    }
}
$currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
$basePath = parse_url(BASE_URL, PHP_URL_PATH) ?? '';
if ($basePath && strpos($currentPath, $basePath) === 0) {
    $currentPath = substr($currentPath, strlen($basePath));
}
$currentPath = '/' . ltrim($currentPath, '/');

if (!function_exists('navbar_build_nav_url')) {
    function navbar_build_nav_url($url)
    {
        if (!$url) {
            return '#';
        }
        if (preg_match('#^https?://#i', $url)) {
            return $url;
        }
        return url($url);
    }
}

if (!function_exists('navbar_is_nav_active')) {
    function navbar_is_nav_active($itemPath, $currentPath, $page, $itemUrl)
    {
        $normalizedItem = '/' . ltrim(parse_url($itemPath ?: $itemUrl, PHP_URL_PATH) ?? '', '/');
        if ($normalizedItem === '/home') {
            $normalizedItem = '/';
        }
        if ($normalizedItem === '/' && ($page === 'home' || $currentPath === '/')) {
            return true;
        }
        if ($normalizedItem !== '/' && $normalizedItem === $currentPath) {
            return true;
        }
        return false;
    }
}
?>

<nav class="navbar navbar-expand-lg navbar-light fixed-top" id="mainNavbar">
    <div class="container">
        <!-- Brand / Logo -->
        <a class="navbar-brand d-flex align-items-center" href="<?= url() ?>">
            <?php if ($brandLogoUrl !== ''): ?>
                <img src="<?= htmlspecialchars($brandLogoUrl) ?>" alt="<?= htmlspecialchars($brandTitle) ?>"
                     class="navbar-logo me-2" height="40">
            <?php else: ?>
                <span class="navbar-brand-text fw-bold mb-0"><?= htmlspecialchars($brandTitle) ?></span>
            <?php endif; ?>
        </a>

        <!-- Mobile Menu Button -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" 
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <!-- Navigation Links -->
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav mx-auto">
                <?php if (!empty($navigationMenu)): ?>
                    <?php foreach ($navigationMenu as $item): ?>
                        <?php
                        $href = navbar_build_nav_url($item['url'] ?? '#');
                        $isActive = navbar_is_nav_active($item['url'] ?? '', $currentPath, $page ?? '', $item['url'] ?? '');
                        ?>
                        <li class="nav-item">
                            <a class="nav-link <?= $isActive ? 'active' : '' ?>" 
                               href="<?= htmlspecialchars($href) ?>" 
                               target="<?= htmlspecialchars($item['target'] ?? '_self') ?>">
                                <?= htmlspecialchars($item['title'] ?? 'Menu Item') ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link <?= ($page ?? '') === 'home' ? 'active' : '' ?>" 
                           href="<?= url() ?>">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= ($page ?? '') === 'about' ? 'active' : '' ?>" 
                           href="<?= url('about') ?>">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= ($page ?? '') === 'portfolio' || ($page ?? '') === 'project-detail' ? 'active' : '' ?>" 
                           href="<?= url('portfolio') ?>">Portfolio</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= ($page ?? '') === 'services' ? 'active' : '' ?>" 
                           href="<?= url('services') ?>">Services</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= ($page ?? '') === 'contact' ? 'active' : '' ?>" 
                           href="<?= url('contact') ?>">Contact</a>
                    </li>
                <?php endif; ?>
            </ul>
            
            <!-- Theme Toggle & CTA -->
            <div class="navbar-nav ms-auto d-flex align-items-center">
                <!-- Theme Toggle -->
                <div class="nav-item me-3">
                    <button class="btn btn-link nav-link theme-toggle p-2" id="themeToggle" 
                            aria-label="Toggle theme" data-bs-toggle="tooltip" data-bs-placement="bottom" 
                            title="Toggle Light/Dark Mode">
                        <i class="bi bi-sun-fill theme-icon-light"></i>
                        <i class="bi bi-moon-fill theme-icon-dark"></i>
                    </button>
                </div>
                
                <!-- CTA Button -->
                <div class="nav-item">
                    <?php $ctaUrl = navbar_build_nav_url($siteSettings['nav_cta_url'] ?? '/contact'); ?>
                    <a href="<?= htmlspecialchars($ctaUrl) ?>" class="btn btn-primary btn-sm px-3">
                        <i class="bi bi-envelope me-1"></i>
                        <?= htmlspecialchars($siteSettings['nav_cta_text'] ?? "Let's Talk") ?>
                    </a>
                </div>
            </div>
        </div>
    </div>
</nav>

<!-- Flash Messages -->
<?php if (isset($this) && method_exists($this, 'hasFlash') && $this->hasFlash()): ?>
    <div class="flash-messages">
        <?php 
        $flash = $this->getFlash();
        foreach ($flash as $type => $message): 
            if ($type === 'form_errors' || $type === 'form_data') continue;
            $alertClass = $type === 'error' ? 'danger' : $type;
        ?>
            <div class="alert alert-<?= $alertClass ?> alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($message) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>