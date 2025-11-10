<!-- Portfolio Header -->
<section class="portfolio-header py-5 mt-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto text-center" data-aos="fade-up">
                <h1 class="portfolio-title mb-4">My Portfolio</h1>
                <p class="portfolio-subtitle text-muted mb-5">
                    A collection of my recent work spanning UI/UX design, branding, and digital experiences. 
                    Each project tells a story of problem-solving and creative solutions.
                </p>
            </div>
        </div>
        
        <!-- Filter Navigation -->
        <div class="row">
            <div class="col-12">
                <div class="portfolio-filters text-center mb-5" data-aos="fade-up" data-aos-delay="200">
                    <a href="<?= url('portfolio') ?>" 
                       class="filter-btn <?= empty($current_category) && empty($search_query) ? 'active' : '' ?>">
                        All Projects
                    </a>
                    <?php if (!empty($categories)): ?>
                        <?php foreach ($categories as $category): ?>
                            <a href="<?= url('portfolio?category=' . urlencode($category)) ?>" 
                               class="filter-btn <?= $current_category === $category ? 'active' : '' ?>">
                                <?= htmlspecialchars($category) ?>
                            </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                
                <!-- Search Bar -->
                <div class="portfolio-search text-center mb-5" data-aos="fade-up" data-aos-delay="300">
                    <form method="GET" action="<?= url('portfolio') ?>" class="search-form">
                        <div class="input-group mx-auto" style="max-width: 400px;">
                            <input type="text" class="form-control" name="search" 
                                   placeholder="Search projects..." 
                                   value="<?= htmlspecialchars($search_query ?? '') ?>">
                            <button class="btn btn-primary" type="submit">
                                <i class="bi bi-search"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Portfolio Grid -->
<section class="portfolio-grid py-5">
    <div class="container">
        <?php if (!empty($projects)): ?>
            <div class="row">
                <?php foreach ($projects as $index => $project): ?>
                    <div class="col-lg-4 col-md-6 mb-5" data-aos="fade-up" data-aos-delay="<?= (int)$index * 100 ?>">
                        <div class="portfolio-item">
                            <div class="portfolio-image">
                                <?php $projectImage = !empty($project['main_image_path']) ? media_url($project['main_image_path']) : asset('images/projects/default.jpg'); ?>
                                <img src="<?= htmlspecialchars($projectImage) ?>" 
                                     alt="<?= htmlspecialchars($project['title'] ?? 'Project') ?>" 
                                     class="img-fluid">
                                <div class="portfolio-overlay">
                                    <div class="portfolio-info">
                                        <h4 class="portfolio-item-title">
                                            <?= htmlspecialchars($project['title'] ?? 'Project') ?>
                                        </h4>
                                        <p class="portfolio-item-category">
                                            <?= htmlspecialchars($project['category'] ?? '') ?>
                                        </p>
                                    </div>
                                    <div class="portfolio-actions">
                                        <a href="<?= url('portfolio/' . ($project['slug'] ?? $project['id'])) ?>" 
                                           class="btn btn-primary btn-sm">
                                            <i class="bi bi-eye me-1"></i>
                                            View Details
                                        </a>
                                        <?php if (!empty($project['project_url'])): ?>
                                            <a href="<?= htmlspecialchars($project['project_url']) ?>" 
                                               class="btn btn-outline-light btn-sm ms-2" 
                                               target="_blank">
                                                <i class="bi bi-box-arrow-up-right"></i>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="portfolio-content mt-3">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <span class="badge bg-primary-subtle text-primary">
                                        <?= htmlspecialchars($project['category'] ?? 'Project') ?>
                                    </span>
                                    <?php if (!empty($project['featured'])): ?>
                                        <span class="badge bg-warning-subtle text-warning">
                                            <i class="bi bi-star-fill me-1"></i>Featured
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <h4 class="portfolio-item-title mb-2">
                                    <a href="<?= url('portfolio/' . ($project['slug'] ?? $project['id'])) ?>">
                                        <?= htmlspecialchars($project['title'] ?? 'Project') ?>
                                    </a>
                                </h4>
                                <p class="portfolio-item-description text-muted mb-2">
                                    <?= htmlspecialchars($project['short_description'] ?? '') ?>
                                </p>
                                <?php $showClient = !empty($project['client_name']) && (($project['client_visibility'] ?? 'yes') !== 'no'); ?>
                                <?php if ($showClient): ?>
                                    <p class="portfolio-item-client mb-0">
                                        <small class="text-muted">
                                            <i class="bi bi-building me-1"></i>
                                            <?= htmlspecialchars($project['client_name']) ?>
                                        </small>
                                    </p>
                                <?php endif; ?>
                                
                                <!-- Technologies -->
                                <?php
                                $technologies = $project['technologies_list'] ?? null;
                                if ($technologies === null) {
                                    $raw = $project['technologies'] ?? null;
                                    $decoded = is_string($raw) ? json_decode($raw, true) : [];
                                    $technologies = json_last_error() === JSON_ERROR_NONE && is_array($decoded) ? $decoded : [];
                                }
                                if (!empty($technologies)):
                                ?>
                                    <div class="portfolio-technologies mt-2">
                                        <?php foreach (array_slice($technologies, 0, 3) as $tech): ?>
                                            <span class="tech-tag"><?= htmlspecialchars($tech) ?></span>
                                        <?php endforeach; ?>
                                        <?php if (count($technologies) > 3): ?>
                                            <span class="tech-tag-more">+<?= count($technologies) - 3 ?></span>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="row">
                <div class="col-12 text-center py-5">
                    <div class="no-results" data-aos="fade-up">
                        <i class="bi bi-search display-1 text-muted mb-3"></i>
                        <h3 class="text-muted mb-3">No Projects Found</h3>
                        <p class="text-muted mb-4">
                            <?php if ($search_query): ?>
                                No projects match your search "<?= htmlspecialchars($search_query) ?>".
                            <?php elseif ($current_category): ?>
                                No projects found in "<?= htmlspecialchars($current_category) ?>".
                            <?php else: ?>
                                No projects are currently available.
                            <?php endif; ?>
                        </p>
                        <a href="<?= url('portfolio') ?>" class="btn btn-primary">
                            <i class="bi bi-arrow-left me-2"></i>
                            View All Projects
                        </a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- CTA Section -->
<section class="cta-section py-5 bg-light">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto text-center" data-aos="fade-up">
                <h2 class="cta-title mb-3">Like What You See?</h2>
                <p class="cta-subtitle text-muted mb-4">
                    I'd love to hear about your project and discuss how we can work together 
                    to create something amazing.
                </p>
                <div class="cta-actions">
                    <a href="<?= url('contact') ?>" class="btn btn-primary btn-lg me-3">
                        <i class="bi bi-envelope me-2"></i>
                        Start a Project
                    </a>
                    <a href="<?= url('services') ?>" class="btn btn-outline-primary btn-lg">
                        <i class="bi bi-list-check me-2"></i>
                        View Services
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>