<?php
$hero = $hero ?? [];
$heroStats = $hero_stats ?? [];
$siteSettings = $siteSettings ?? [];
$primaryCta = $hero['hero_primary_cta'] ?? [
    'text' => $hero['hero_primary_cta_text'] ?? 'View My Work',
    'url' => $hero['hero_primary_cta_url'] ?? '/portfolio'
];
$secondaryCta = $hero['hero_secondary_cta'] ?? [
    'text' => $siteSettings['nav_cta_text'] ?? "Let's Talk",
    'url' => $hero['hero_secondary_cta_url'] ?? '/contact'
];
$primaryCtaUrl = navbar_build_nav_url($primaryCta['url'] ?? '/portfolio');
$primaryCtaText = $primaryCta['text'] ?? 'View My Work';
$secondaryCtaText = $secondaryCta['text'] ?? "Let's Talk";
$secondaryCtaUrl = navbar_build_nav_url($secondaryCta['url'] ?? '/contact');
$heroImage = !empty($hero['hero_background_image_url']) ? $hero['hero_background_image_url'] : asset('images/hero-portrait.jpg');
$heroImageAlt = $hero['hero_background_image_alt'] ?? 'Hero portrait';
$heroIntroPrefix = $hero['hero_intro_prefix'] ?? "Hi, I'm";
$heroIntroNameFirst = $hero['hero_intro_name_first'] ?? '';
$heroIntroNameRest = $hero['hero_intro_name_rest'] ?? '';
$heroIntroSuffix = $hero['hero_intro_suffix'] ?? '';
$scrollIndicatorText = trim($hero['scroll_indicator_text'] ?? 'Scroll to explore');
$featuredProjects = $featured_projects ?? [];
$servicesList = $services ?? [];
$skillsByCategory = $skills_by_category ?? [];
$featuredTestimonials = $featured_testimonials ?? [];
$pageSections = $page_sections ?? [];

$ctaDefaults = [
    'title' => 'Ready to Start Your Project?',
    'subtitle' => "Let's partner to build inclusive, outcome-driven experiences.",
    'primary_cta_text' => 'Get In Touch',
    'primary_cta_url' => '/contact',
    'secondary_cta_text' => 'View Services',
    'secondary_cta_url' => '/services'
];

$ctaSection = [];
if (isset($pageSections['page_cta']) && is_array($pageSections['page_cta'])) {
    $ctaSection = $pageSections['page_cta'];
}

$ctaData = array_merge($ctaDefaults, array_intersect_key($ctaSection, $ctaDefaults));

$ctaTitle = trim((string)$ctaData['title']);
if ($ctaTitle === '') {
    $ctaTitle = $ctaDefaults['title'];
}

$ctaSubtitle = trim((string)$ctaData['subtitle']);

$ctaPrimaryText = trim((string)$ctaData['primary_cta_text']);
if ($ctaPrimaryText === '') {
    $ctaPrimaryText = $ctaDefaults['primary_cta_text'];
}

$ctaPrimaryUrlRaw = (string)$ctaData['primary_cta_url'];
$ctaPrimaryUrl = navbar_build_nav_url($ctaPrimaryUrlRaw !== '' ? $ctaPrimaryUrlRaw : $ctaDefaults['primary_cta_url']);

$ctaSecondaryText = trim((string)$ctaData['secondary_cta_text']);
$ctaSecondaryUrlRaw = trim((string)$ctaData['secondary_cta_url']);
$ctaSecondaryUrl = ($ctaSecondaryText !== '' && $ctaSecondaryUrlRaw !== '')
    ? navbar_build_nav_url($ctaSecondaryUrlRaw)
    : '';
?>

<!-- Hero Section -->
<section class="hero-section d-flex align-items-center">
    <div class="container">
        <div class="row align-items-center min-vh-100">
            <div class="col-lg-6" data-aos="fade-right">
                <div class="hero-content">
                    <?php if (!empty($heroIntroPrefix) || !empty($heroIntroNameFirst)): ?>
                        <div class="hero-kicker fw-semibold mb-3">
                            <span class="hero-kicker-prefix"><?= htmlspecialchars($heroIntroPrefix) ?></span>
                            <?php if (!empty($heroIntroNameFirst)): ?>
                                <span class="hero-kicker-name">
                                    <span class="hero-kicker-name-first"><?= htmlspecialchars($heroIntroNameFirst) ?></span>
                                    <?php if (!empty($heroIntroNameRest)): ?>
                                        <span class="hero-kicker-name-last"><?= htmlspecialchars($heroIntroNameRest) ?></span>
                                    <?php endif; ?>
                                </span>
                            <?php endif; ?>
                            <?php if (!empty($heroIntroSuffix)): ?>
                                <span class="hero-kicker-suffix"><?= htmlspecialchars($heroIntroSuffix) ?></span>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                    <h1 class="hero-title mb-4">
                        <?= htmlspecialchars($hero['hero_title'] ?? 'Creative UI/UX Designer') ?>
                    </h1>
                    <?php if (!empty($hero['hero_subtitle'])): ?>
                        <p class="hero-subtitle h5 text-muted mb-3">
                            <?= htmlspecialchars($hero['hero_subtitle']) ?>
                        </p>
                    <?php endif; ?>
                    <p class="hero-description mb-4 text-muted">
                        <?= nl2br(htmlspecialchars($hero['hero_description'] ?? 'I design intuitive and beautiful digital products that solve problems and delight users.')) ?>
                    </p>
                    <div class="hero-actions">
                        <a href="<?= htmlspecialchars($primaryCtaUrl) ?>" class="btn btn-primary btn-lg me-3">
                            <i class="bi bi-collection me-2"></i>
                            <?= htmlspecialchars($primaryCtaText) ?>
                        </a>
                        <a href="<?= htmlspecialchars($secondaryCtaUrl) ?>" class="btn btn-outline-primary btn-lg">
                            <i class="bi bi-chat-dots me-2"></i>
                            <?= htmlspecialchars($secondaryCtaText) ?>
                        </a>
                    </div>
                    
                    <?php if (!empty($heroStats)): ?>
                        <div class="hero-stats mt-5 pt-4 border-top">
                            <div class="row g-3">
                                <?php foreach (array_slice($heroStats, 0, 3) as $stat): ?>
                                    <div class="col-4">
                                        <div class="stat-item">
                                            <h3 class="stat-number text-primary"><?= htmlspecialchars($stat['value']) ?></h3>
                                            <p class="stat-label text-muted mb-0"><?= htmlspecialchars($stat['label']) ?></p>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-lg-6" data-aos="fade-left" data-aos-delay="200">
                <div class="hero-image">
                    <div class="hero-image-wrapper">
                    <img src="<?= htmlspecialchars($heroImage) ?>" alt="<?= htmlspecialchars($heroImageAlt) ?>"
                        class="img-fluid rounded-3">
                        <div class="hero-decoration">
                            <div class="decoration-circle decoration-circle-1"></div>
                            <div class="decoration-circle decoration-circle-2"></div>
                            <div class="decoration-square"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Scroll Indicator -->
    <?php if ($scrollIndicatorText !== ''): ?>
        <div class="scroll-indicator">
            <div class="scroll-mouse">
                <div class="scroll-wheel"></div>
            </div>
            <p class="scroll-text"><?= htmlspecialchars($scrollIndicatorText) ?></p>
        </div>
    <?php endif; ?>
</section>

<!-- Featured Work Section -->
<section class="featured-work-section py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto text-center" data-aos="fade-up">
                <h2 class="section-title mb-3">Featured Work</h2>
                <p class="section-subtitle text-muted mb-5">
                    Recent projects that highlight my approach to user-centered design and problem-solving.
                </p>
            </div>
        </div>
        
        <div class="row">
            <?php if (!empty($featuredProjects)): ?>
                <?php foreach ($featuredProjects as $index => $project): ?>
                    <?php
                    $projectImage = $project['main_image_url'] ?? asset('images/projects/default.jpg');
                    $projectUrl = url('portfolio/' . ($project['slug'] ?? $project['id']));
                    $liveUrl = $project['project_url'] ?? '';
                    ?>
                    <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="<?= (int)$index * 100 ?>">
                        <div class="project-card">
                            <div class="project-image">
                                <img src="<?= htmlspecialchars($projectImage) ?>" 
                                     alt="<?= htmlspecialchars($project['title'] ?? 'Project') ?>" 
                                     class="img-fluid">
                                <div class="project-overlay">
                                    <div class="project-actions">
                                        <a href="<?= htmlspecialchars($projectUrl) ?>" 
                                           class="btn btn-primary btn-sm">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <?php if (!empty($liveUrl)): ?>
                                            <a href="<?= htmlspecialchars($liveUrl) ?>" 
                                               class="btn btn-outline-light btn-sm ms-2" 
                                               target="_blank" rel="noopener">
                                                <i class="bi bi-box-arrow-up-right"></i>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="project-content">
                                <?php if (!empty($project['category'])): ?>
                                    <div class="project-category">
                                        <span class="badge bg-primary-subtle text-primary">
                                            <?= htmlspecialchars($project['category']) ?>
                                        </span>
                                    </div>
                                <?php endif; ?>
                                <h4 class="project-title">
                                    <a href="<?= htmlspecialchars($projectUrl) ?>">
                                        <?= htmlspecialchars($project['title'] ?? 'Project Title') ?>
                                    </a>
                                </h4>
                                <?php if (!empty($project['short_description'])): ?>
                                    <p class="project-description text-muted">
                                        <?= htmlspecialchars($project['short_description']) ?>
                                    </p>
                                <?php endif; ?>
                                <?php $showClient = !empty($project['client_name']) && (($project['client_visibility'] ?? 'yes') !== 'no'); ?>
                                <?php if ($showClient): ?>
                                    <p class="project-client">
                                        <small class="text-muted">
                                            <i class="bi bi-building me-1"></i>
                                            <?= htmlspecialchars($project['client_name']) ?>
                                        </small>
                                    </p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center">
                    <p class="text-muted">No featured projects available at the moment.</p>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="row mt-4">
            <div class="col-12 text-center" data-aos="fade-up">
                <a href="<?= url('portfolio') ?>" class="btn btn-outline-primary btn-lg">
                    <i class="bi bi-grid me-2"></i>
                    View All Projects
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Services Section -->
<section class="services-section py-5 bg-light">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto text-center" data-aos="fade-up">
                <h2 class="section-title mb-3">Services</h2>
                <p class="section-subtitle text-muted mb-5">
                    A selection of services that help teams launch thoughtful, user-first experiences.
                </p>
            </div>
        </div>
        <div class="row">
            <?php if (!empty($servicesList)): ?>
                <?php foreach (array_slice($servicesList, 0, 4) as $index => $service): ?>
                    <?php
                    $featuresRaw = $service['features'] ?? [];
                    $features = [];
                    if (is_array($featuresRaw)) {
                        foreach ($featuresRaw as $feature) {
                            if (is_array($feature) && !empty($feature['feature_text'])) {
                                $features[] = $feature['feature_text'];
                            } elseif (is_string($feature)) {
                                $features[] = $feature;
                            }
                        }
                    }
                    ?>
                    <div class="col-lg-6 mb-4" data-aos="fade-up" data-aos-delay="<?= (int)$index * 100 ?>">
                        <div class="service-card" id="service-<?= htmlspecialchars($service['id']) ?>">
                            <div class="service-icon">
                                <i class="bi <?= htmlspecialchars($service['icon'] ?? 'bi-lightning-charge') ?>"></i>
                            </div>
                            <div class="service-content">
                                <h3 class="service-title"><?= htmlspecialchars($service['title'] ?? 'Service') ?></h3>
                                <?php if (!empty($service['description'])): ?>
                                    <p class="service-description text-muted">
                                        <?= htmlspecialchars($service['description']) ?>
                                    </p>
                                <?php endif; ?>
                                <?php if (!empty($features)): ?>
                                    <ul class="service-features">
                                        <?php foreach (array_slice($features, 0, 5) as $feature): ?>
                                            <li><i class="bi bi-check2 text-primary me-2"></i><?= htmlspecialchars($feature) ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center">
                    <p class="text-muted">Add services from the admin panel to highlight what you offer.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Skills & Tools Section -->
<section class="skills-section py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto text-center" data-aos="fade-up">
                <h2 class="section-title mb-3">Skills &amp; Tools</h2>
                <p class="section-subtitle text-muted mb-5">
                    Capabilities and toolsets that keep projects moving fast without compromising quality.
                </p>
            </div>
        </div>
        <div class="row">
            <?php if (!empty($skillsByCategory)): ?>
                <?php $delay = 100; ?>
                <?php foreach ($skillsByCategory as $categoryName => $group): ?>
                    <?php
                    $categoryMeta = $group['meta'] ?? [];
                    $skills = $group['items'] ?? [];

                    if (empty($skills)) {
                        continue;
                    }

                    $iconClass = trim((string)($categoryMeta['icon_class'] ?? ''));
                    $hasBootstrapIcon = strpos($iconClass, 'bi-') === 0;
                    $resolvedIconClass = $iconClass !== ''
                        ? ($hasBootstrapIcon ? 'bi ' . $iconClass : $iconClass)
                        : 'bi bi-palette';
                    ?>
                    <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="<?= $delay ?>">
                        <div class="skill-category">
                            <div class="skill-icon">
                                <i class="<?= htmlspecialchars($resolvedIconClass) ?>"></i>
                            </div>
                            <h4 class="skill-title"><?= htmlspecialchars($categoryName ?? 'Skillset') ?></h4>
                            <div class="skill-list">
                                <?php foreach (array_slice($skills, 0, 8) as $skill): ?>
                                    <span class="skill-item">
                                        <?= htmlspecialchars($skill['skill_name'] ?? $skill['name'] ?? '') ?>
                                        <?php if (!empty($skill['proficiency_level'])): ?>
                                            <small class="text-muted"><?= (int)$skill['proficiency_level'] ?>%</small>
                                        <?php endif; ?>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    <?php $delay += 100; ?>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center">
                    <p class="text-muted">Keep skills up to date from the admin panel to showcase your expertise.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Testimonials Section -->
<section class="testimonials-section py-5 bg-light">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto text-center" data-aos="fade-up">
                <h2 class="section-title mb-3">What Clients Say</h2>
                <p class="section-subtitle text-muted mb-5">
                    Feedback from partners and teams I have collaborated with.
                </p>
            </div>
        </div>
        <div class="row">
            <?php if (!empty($featuredTestimonials)): ?>
                <?php foreach ($featuredTestimonials as $index => $testimonial): ?>
                    <?php
                    $testimonialText = $testimonial['testimonial_text'] ?? '';
                    $avatar = !empty($testimonial['image_url']) ? $testimonial['image_url'] : asset('images/testimonials/default-avatar.jpg');
                    $rating = (int)($testimonial['rating'] ?? 5);
                    ?>
                    <div class="col-lg-4 mb-4" data-aos="fade-up" data-aos-delay="<?= (int)$index * 100 ?>">
                        <div class="testimonial-card">
                            <div class="testimonial-content">
                                <?php if ($rating > 0): ?>
                                    <div class="testimonial-stars mb-3">
                                        <?php for ($i = 0; $i < 5; $i++): ?>
                                            <i class="bi <?= $i < $rating ? 'bi-star-fill text-warning' : 'bi-star text-warning' ?>"></i>
                                        <?php endfor; ?>
                                    </div>
                                <?php endif; ?>
                                <p class="testimonial-text">
                                    "<?= htmlspecialchars($testimonialText) ?>"
                                </p>
                            </div>
                            <div class="testimonial-author">
                                <img src="<?= htmlspecialchars($avatar) ?>" alt="<?= htmlspecialchars($testimonial['client_name'] ?? 'Client') ?>" 
                                     class="testimonial-avatar">
                                <div class="author-info">
                                    <h6 class="author-name"><?= htmlspecialchars($testimonial['client_name'] ?? 'Client Name') ?></h6>
                                    <p class="author-title">
                                        <?= htmlspecialchars(trim(($testimonial['client_position'] ?? '') . ' ' . ($testimonial['client_company'] ?? ''))) ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center">
                    <p class="text-muted">Collect testimonials in the admin area to build trust on the homepage.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="cta-section py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto text-center" data-aos="fade-up">
                <h2 class="cta-title mb-3"><?= htmlspecialchars($ctaTitle) ?></h2>
                <?php if ($ctaSubtitle !== ''): ?>
                    <p class="cta-subtitle text-muted mb-4">
                        <?= htmlspecialchars($ctaSubtitle) ?>
                    </p>
                <?php endif; ?>
                <div class="cta-actions">
                    <a href="<?= htmlspecialchars($ctaPrimaryUrl) ?>" class="btn btn-primary btn-lg me-3">
                        <i class="bi bi-envelope me-2"></i>
                        <?= htmlspecialchars($ctaPrimaryText) ?>
                    </a>
                    <?php if ($ctaSecondaryUrl !== '' && $ctaSecondaryText !== ''): ?>
                        <a href="<?= htmlspecialchars($ctaSecondaryUrl) ?>" class="btn btn-outline-primary btn-lg">
                            <i class="bi bi-list-check me-2"></i>
                            <?= htmlspecialchars($ctaSecondaryText) ?>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>