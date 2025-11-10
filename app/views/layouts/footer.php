<?php
$siteSettings = $siteSettings ?? [];
$footerSettings = $footerSettings ?? [];
$contactSettings = $contactSettings ?? [];
$socialLinks = $socialLinks ?? [];
$navigationMenu = $navigationMenu ?? [];
$activeServices = $activeServices ?? [];

$brandTitle = $siteSettings['site_title'] ?? 'Yahya Bouhafs';
$brandTagline = $siteSettings['site_tagline'] ?? '';
$brandParts = explode(' ', trim($brandTitle), 2);
$brandPrimary = $brandParts[0] ?? 'Yahya';
$brandSecondary = $brandParts[1] ?? '';
$brandLogo = $siteSettings['site_logo'] ?? '';
$siteDescription = $siteSettings['site_description'] ?? 'Creating intuitive and beautiful digital experiences through user-centered design.';

$contactEmail = $contactSettings['contact_email'] ?? '';
$contactPhone = $contactSettings['contact_phone'] ?? '';
$contactAddress = $contactSettings['contact_address'] ?? '';

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

if (!function_exists('footer_social_icon')) {
    function footer_social_icon($platform)
    {
        $map = [
            'facebook' => 'bi-facebook',
            'twitter' => 'bi-twitter',
            'linkedin' => 'bi-linkedin',
            'instagram' => 'bi-instagram',
            'dribbble' => 'bi-dribbble',
            'behance' => 'bi-behance',
            'github' => 'bi-github',
            'youtube' => 'bi-youtube'
        ];
        return $map[$platform] ?? 'bi-globe';
    }
}

$footerLinks = [];
if (!empty($footerSettings['footer_links']) && is_array($footerSettings['footer_links'])) {
    foreach ($footerSettings['footer_links'] as $link) {
        $label = $link['label'] ?? $link['title'] ?? '';
        if (empty($label) || empty($link['url'])) {
            continue;
        }
        $footerLinks[] = array_merge($link, ['label' => $label]);
    }
}
?>

<footer class="footer bg-dark">
    <div class="container">
        <!-- Main Footer Content -->
        <div class="row py-5">
            <!-- Brand & Description -->
            <div class="col-lg-4 col-md-6 mb-4 mb-lg-0">
                <div class="footer-brand mb-3">
                    <?php if (!empty($brandLogo)): ?>
                        <img src="<?= media_url($brandLogo) ?>" alt="<?= htmlspecialchars($brandTitle) ?>" class="footer-logo">
                    <?php else: ?>
                        <span class="brand-text text-white"><?= htmlspecialchars($brandPrimary) ?></span>
                        <?php if ($brandSecondary): ?>
                            <span class="brand-accent"><?= htmlspecialchars($brandSecondary) ?></span>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
                <?php if ($brandTagline): ?>
                    <p class="text-muted mb-2"><?= htmlspecialchars($brandTagline) ?></p>
                <?php endif; ?>
                <p class="text-muted mb-4">
                    <?= htmlspecialchars($siteDescription) ?>
                </p>
                <?php if (!empty($socialLinks) && (!isset($footerSettings['footer_show_social']) || $footerSettings['footer_show_social'])): ?>
                    <div class="social-links">
                        <?php foreach ($socialLinks as $link): ?>
                            <a href="<?= htmlspecialchars($link['url']) ?>" class="social-link me-3" target="_blank" rel="noopener"
                               aria-label="<?= htmlspecialchars(ucfirst($link['platform'])) ?>">
                                <i class="bi <?= footer_social_icon($link['platform']) ?>"></i>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Quick Links -->
            <div class="col-lg-2 col-md-6 mb-4 mb-lg-0">
                <h6 class="footer-title mb-3">Quick Links</h6>
                <ul class="footer-links">
                    <?php if (!empty($navigationMenu)): ?>
                        <?php foreach (array_slice($navigationMenu, 0, 6) as $item): ?>
                            <li><a href="<?= htmlspecialchars(navbar_build_nav_url($item['url'] ?? '#')) ?>" target="<?= htmlspecialchars($item['target'] ?? '_self') ?>"><?= htmlspecialchars($item['title'] ?? 'Link') ?></a></li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <li><a href="<?= url() ?>">Home</a></li>
                        <li><a href="<?= url('about') ?>">About</a></li>
                        <li><a href="<?= url('portfolio') ?>">Portfolio</a></li>
                        <li><a href="<?= url('services') ?>">Services</a></li>
                        <li><a href="<?= url('contact') ?>">Contact</a></li>
                    <?php endif; ?>
                </ul>
            </div>
            
            <!-- Services -->
            <div class="col-lg-3 col-md-6 mb-4 mb-lg-0">
                <h6 class="footer-title mb-3">Services</h6>
                <ul class="footer-links">
                    <?php if (!empty($activeServices)): ?>
                        <?php foreach (array_slice($activeServices, 0, 6) as $service): ?>
                            <li><a href="<?= url('services') ?>#service-<?= htmlspecialchars($service['id']) ?>"><?= htmlspecialchars($service['title'] ?? 'Service') ?></a></li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <li><a href="<?= url('services') ?>">Explore Services</a></li>
                    <?php endif; ?>
                </ul>
            </div>
            
            <!-- Contact Info -->
            <div class="col-lg-3 col-md-6">
                <h6 class="footer-title mb-3">Get In Touch</h6>
                <div class="contact-info">
                    <?php if ($contactEmail): ?>
                        <div class="contact-item mb-2">
                            <i class="bi bi-envelope me-2"></i>
                            <a href="mailto:<?= htmlspecialchars($contactEmail) ?>"><?= htmlspecialchars($contactEmail) ?></a>
                        </div>
                    <?php endif; ?>
                    <?php if ($contactPhone): ?>
                        <div class="contact-item mb-2">
                            <i class="bi bi-telephone me-2"></i>
                            <a href="tel:<?= htmlspecialchars($contactPhone) ?>"><?= htmlspecialchars($contactPhone) ?></a>
                        </div>
                    <?php endif; ?>
                    <?php if ($contactAddress): ?>
                        <div class="contact-item mb-3">
                            <i class="bi bi-geo-alt me-2"></i>
                            <span><?= nl2br(htmlspecialchars($contactAddress)) ?></span>
                        </div>
                    <?php endif; ?>
                    <a href="<?= url('contact') ?>" class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-chat-dots me-1"></i>
                        <?= htmlspecialchars($footerSettings['footer_cta_text'] ?? 'Start a Project') ?>
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Footer Bottom -->
        <div class="footer-bottom py-3 border-top border-secondary">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <p class="text-muted mb-0">
                        <?= htmlspecialchars($footerSettings['footer_text'] ?? ('© ' . date('Y') . ' ' . $brandTitle . '. All rights reserved.')) ?>
                    </p>
                </div>
                <div class="col-md-6 text-md-end">
                    <div class="footer-links-inline">
                        <?php if (!empty($footerLinks)): ?>
                            <?php foreach ($footerLinks as $link): ?>
                                <a href="<?= htmlspecialchars($link['url']) ?>" class="text-muted me-3" target="<?= htmlspecialchars($link['target'] ?? '_self') ?>">
                                    <?= htmlspecialchars($link['label']) ?>
                                </a>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <a href="<?= url('privacy') ?>" class="text-muted me-3">Privacy Policy</a>
                            <a href="<?= url('terms') ?>" class="text-muted">Terms of Service</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</footer>