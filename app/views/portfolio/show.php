<?php $showClient = !empty($project['client_name']) && (($project['client_visibility'] ?? 'yes') !== 'no'); ?>
<!-- Project Header -->
<section class="project-header py-5 mt-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto text-center" data-aos="fade-up">
                <div class="project-breadcrumb mb-3">
                    <a href="<?= url('portfolio') ?>" class="text-muted">
                        <i class="bi bi-arrow-left me-1"></i>
                        Back to Portfolio
                    </a>
                </div>
                <div class="project-category mb-3">
                    <span class="badge bg-primary-subtle text-primary fs-6">
                        <?= htmlspecialchars($project['category'] ?? '') ?>
                    </span>
                </div>
                <h1 class="project-title mb-4"><?= htmlspecialchars($project['title'] ?? 'Project') ?></h1>
                <p class="project-subtitle text-muted mb-4">
                    <?= htmlspecialchars($project['short_description'] ?? '') ?>
                </p>
                
                <!-- Project Meta -->
                <div class="project-meta">
                    <div class="row justify-content-center">
                        <?php if ($showClient): ?>
                        <div class="col-auto">
                            <div class="meta-item">
                                <i class="bi bi-building text-primary"></i>
                                <strong>Client:</strong> <?= htmlspecialchars($project['client_name']) ?>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <div class="col-auto">
                            <div class="meta-item">
                                <i class="bi bi-calendar text-primary"></i>
                                <?php $projectYear = !empty($project['created_at']) ? date('Y', strtotime($project['created_at'])) : date('Y'); ?>
                                <strong>Year:</strong> <?= htmlspecialchars($projectYear) ?>
                            </div>
                        </div>
                        
                        <?php if ($project['project_url']): ?>
                        <div class="col-auto">
                            <div class="meta-item">
                                <a href="<?= htmlspecialchars($project['project_url']) ?>" target="_blank" class="btn btn-outline-primary btn-sm">
                                    <i class="bi bi-box-arrow-up-right me-1"></i>
                                    View Live Project
                                </a>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Project Image -->
<section class="project-hero-image">
    <div class="container">
        <div class="row">
            <div class="col-12" data-aos="fade-up" data-aos-delay="200">
                <div class="project-image-wrapper">
                <?php $heroImage = !empty($project['main_image_path']) ? media_url($project['main_image_path']) : asset('images/projects/default.jpg'); ?>
                <img src="<?= htmlspecialchars($heroImage) ?>" 
                    alt="<?= htmlspecialchars($project['title'] ?? 'Project image') ?>" 
                         class="img-fluid rounded-3 shadow-lg">
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Project Details -->
<section class="project-details py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-8">
                <!-- Project Description -->
                <div class="project-section mb-5" data-aos="fade-up">
                    <h2 class="section-title mb-4">Project Overview</h2>
                    <div class="project-description">
                        <?= nl2br(htmlspecialchars($project['description'] ?? '')) ?>
                    </div>
                </div>
                
                <!-- Challenge Section -->
                <div class="project-section mb-5" data-aos="fade-up" data-aos-delay="100">
                    <h3 class="section-subtitle mb-3">The Challenge</h3>
                    <p class="text-muted">
                        Every project begins with understanding the core challenges and opportunities. 
                        This project required balancing user needs with business objectives while 
                        maintaining technical feasibility and design excellence.
                    </p>
                </div>
                
                <!-- Solution Section -->
                <div class="project-section mb-5" data-aos="fade-up" data-aos-delay="200">
                    <h3 class="section-subtitle mb-3">Our Solution</h3>
                    <p class="text-muted">
                        Through careful research and iterative design, we developed a solution that 
                        addresses user pain points while exceeding business goals. The final design 
                        demonstrates how thoughtful UX can drive both user satisfaction and business success.
                    </p>
                </div>
                
                <!-- Process Section -->
                <div class="project-section mb-5" data-aos="fade-up" data-aos-delay="300">
                    <h3 class="section-subtitle mb-3">Design Process</h3>
                    <div class="process-steps">
                        <div class="process-step-item mb-3">
                            <div class="step-number">1</div>
                            <div class="step-content">
                                <h5>Research & Discovery</h5>
                                <p class="text-muted mb-0">Understanding user needs and business requirements</p>
                            </div>
                        </div>
                        <div class="process-step-item mb-3">
                            <div class="step-number">2</div>
                            <div class="step-content">
                                <h5>Ideation & Wireframing</h5>
                                <p class="text-muted mb-0">Exploring solutions and creating low-fidelity prototypes</p>
                            </div>
                        </div>
                        <div class="process-step-item mb-3">
                            <div class="step-number">3</div>
                            <div class="step-content">
                                <h5>Visual Design</h5>
                                <p class="text-muted mb-0">Developing the visual identity and high-fidelity designs</p>
                            </div>
                        </div>
                        <div class="process-step-item mb-3">
                            <div class="step-number">4</div>
                            <div class="step-content">
                                <h5>Testing & Iteration</h5>
                                <p class="text-muted mb-0">Validating designs with users and refining based on feedback</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Sidebar -->
            <div class="col-lg-4">
                <div class="project-sidebar" data-aos="fade-up" data-aos-delay="400">
                    <!-- Technologies -->
                    <?php if (!empty($technologies)): ?>
                    <div class="sidebar-section mb-4">
                        <h4 class="sidebar-title">Technologies Used</h4>
                        <div class="technology-list">
                            <?php foreach ($technologies as $tech): ?>
                                <span class="technology-tag"><?= htmlspecialchars($tech) ?></span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Project Info -->
                    <div class="sidebar-section mb-4">
                        <h4 class="sidebar-title">Project Details</h4>
                        <div class="project-info-list">
                            <?php if ($showClient): ?>
                            <div class="info-item">
                                <strong>Client:</strong>
                                <span><?= htmlspecialchars($project['client_name']) ?></span>
                            </div>
                            <?php endif; ?>
                            
                            <div class="info-item">
                                <strong>Category:</strong>
                                <span><?= htmlspecialchars($project['category'] ?? '') ?></span>
                            </div>
                            
                            <div class="info-item">
                                <strong>Year:</strong>
                                <span><?= htmlspecialchars($projectYear) ?></span>
                            </div>
                            
                            <?php if (!empty($project['project_url'])): ?>
                            <div class="info-item">
                                <strong>Website:</strong>
                                <a href="<?= htmlspecialchars($project['project_url']) ?>" target="_blank" class="text-primary">
                                    View Live <i class="bi bi-box-arrow-up-right"></i>
                                </a>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Share Project -->
                    <div class="sidebar-section">
                        <h4 class="sidebar-title">Share This Project</h4>
                        <div class="share-buttons">
                            <a href="#" class="share-btn" onclick="shareOnLinkedIn()" aria-label="Share on LinkedIn">
                                <i class="bi bi-linkedin"></i>
                            </a>
                            <a href="#" class="share-btn" onclick="shareOnTwitter()" aria-label="Share on Twitter">
                                <i class="bi bi-twitter"></i>
                            </a>
                            <a href="#" class="share-btn" onclick="copyToClipboard()" aria-label="Copy Link">
                                <i class="bi bi-link-45deg"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Project Gallery -->
<?php if (!empty($gallery)): ?>
<section class="project-gallery py-5 bg-light">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center mb-5" data-aos="fade-up">
                <h2 class="section-title">Project Gallery</h2>
                <p class="section-subtitle text-muted">
                    Additional views and details from the project
                </p>
            </div>
        </div>
        <div class="row">
            <?php foreach ($gallery as $index => $image): ?>
                <div class="col-lg-6 mb-4" data-aos="fade-up" data-aos-delay="<?= (int)$index * 100 ?>">
                    <div class="gallery-item">
                        <?php $galleryImageUrl = !empty($image['image_url']) ? $image['image_url'] : asset('images/projects/default.jpg'); ?>
                        <img src="<?= htmlspecialchars($galleryImageUrl) ?>" 
                             alt="<?= htmlspecialchars(($project['title'] ?? 'Project') . ' - Gallery Image ' . ($index + 1)) ?>" 
                             class="img-fluid rounded-3 shadow-sm">
                        <?php if (!empty($image['caption'])): ?>
                            <div class="gallery-caption text-muted mt-2 small">
                                <?= htmlspecialchars($image['caption']) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Results Section -->
<section class="project-results py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto text-center" data-aos="fade-up">
                <h2 class="section-title mb-4">Results & Impact</h2>
                <p class="section-subtitle text-muted mb-5">
                    The impact of thoughtful design on user experience and business metrics
                </p>
            </div>
        </div>
        
        <div class="row">
            <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="100">
                <div class="result-card text-center">
                    <div class="result-number text-primary">45%</div>
                    <h4 class="result-title">Increase in User Engagement</h4>
                    <p class="result-description text-muted">
                        Users spent significantly more time interacting with the redesigned interface
                    </p>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="200">
                <div class="result-card text-center">
                    <div class="result-number text-primary">32%</div>
                    <h4 class="result-title">Conversion Rate Improvement</h4>
                    <p class="result-description text-muted">
                        Streamlined user flows led to higher conversion rates across key metrics
                    </p>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="300">
                <div class="result-card text-center">
                    <div class="result-number text-primary">28%</div>
                    <h4 class="result-title">Reduction in Support Issues</h4>
                    <p class="result-description text-muted">
                        Improved usability significantly reduced customer support inquiries
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Navigation -->
<section class="project-navigation py-5 bg-light">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-6 text-start" data-aos="fade-right">
                <?php if ($previous_project): ?>
                    <a href="<?= url('portfolio/' . $previous_project['slug']) ?>" class="project-nav-link">
                        <div class="nav-direction">
                            <i class="bi bi-arrow-left me-2"></i>
                            Previous Project
                        </div>
                        <div class="nav-title"><?= htmlspecialchars($previous_project['title']) ?></div>
                    </a>
                <?php endif; ?>
            </div>
            
            <div class="col-md-6 text-end" data-aos="fade-left">
                <?php if ($next_project): ?>
                    <a href="<?= url('portfolio/' . $next_project['slug']) ?>" class="project-nav-link">
                        <div class="nav-direction">
                            Next Project
                            <i class="bi bi-arrow-right ms-2"></i>
                        </div>
                        <div class="nav-title"><?= htmlspecialchars($next_project['title']) ?></div>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="cta-section py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto text-center" data-aos="fade-up">
                <h2 class="cta-title mb-3">Interested in Working Together?</h2>
                <p class="cta-subtitle text-muted mb-4">
                    I'd love to hear about your project and discuss how we can create something amazing together.
                </p>
                <div class="cta-actions">
                    <a href="<?= url('contact') ?>" class="btn btn-primary btn-lg me-3">
                        <i class="bi bi-envelope me-2"></i>
                        Start a Project
                    </a>
                    <a href="<?= url('portfolio') ?>" class="btn btn-outline-primary btn-lg">
                        <i class="bi bi-grid me-2"></i>
                        View More Work
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
function shareOnLinkedIn() {
    const url = encodeURIComponent(window.location.href);
    const title = encodeURIComponent(document.title);
    window.open(`https://www.linkedin.com/sharing/share-offsite/?url=${url}`, '_blank');
}

function shareOnTwitter() {
    const url = encodeURIComponent(window.location.href);
    const title = encodeURIComponent(document.title);
    window.open(`https://twitter.com/intent/tweet?url=${url}&text=${title}`, '_blank');
}

function copyToClipboard() {
    navigator.clipboard.writeText(window.location.href).then(() => {
        // You could show a toast notification here
        alert('Link copied to clipboard!');
    });
}
</script>