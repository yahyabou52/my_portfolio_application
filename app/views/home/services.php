<?php
$pageSections = $page_sections ?? [];
$pageContent = $page_content ?? '';
$servicesList = $services ?? [];
$processSteps = $process_steps ?? [];
$pricingPlans = $pricing_plans ?? [];
$faqsList = $faqs ?? [];
$testimonials = $featured_testimonials ?? [];

$heroSection = $pageSections['hero'] ?? [];
$servicesTitle = $heroSection['title'] ?? 'My Services';
$servicesSubtitle = $heroSection['subtitle'] ?? 'I offer comprehensive design solutions that blend strategy with thoughtful execution.';

$processSection = $pageSections['process'] ?? [];
$processTitle = $processSection['title'] ?? 'My Design Process';
$processSubtitle = $processSection['subtitle'] ?? 'A collaborative, transparent approach keeps teams aligned and projects moving forward.';

$packagesSection = $pageSections['packages'] ?? [];
$packagesTitle = $packagesSection['title'] ?? 'Service Packages';
$packagesSubtitle = $packagesSection['subtitle'] ?? 'Pick the collaboration model that best fits your timeline, scope, and budget.';

$faqSection = $pageSections['faq'] ?? [];
$faqTitle = $faqSection['title'] ?? 'Frequently Asked Questions';
$faqSubtitle = $faqSection['subtitle'] ?? 'Common questions about scope, timelines, pricing, and collaboration.';
?>

<!-- Services Hero -->
<section class="services-hero py-5 mt-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto text-center" data-aos="fade-up">
                <h1 class="services-title mb-4"><?= htmlspecialchars($servicesTitle) ?></h1>
                <?php if (!empty($servicesSubtitle)): ?>
                    <p class="services-subtitle text-muted mb-5">
                        <?= htmlspecialchars($servicesSubtitle) ?>
                    </p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<!-- Main Services -->
<section class="main-services py-5">
    <div class="container">
        <div class="row">
            <?php if (!empty($servicesList)): ?>
                <?php foreach ($servicesList as $index => $service): ?>
                    <?php
                    $serviceId = 'service-' . ($service['id'] ?? $index);
                    $icon = $service['icon'] ?? 'bi-lightning-charge';
                    $priceLabel = $service['price_label'] ?? '';
                    $priceAmount = $service['price_amount'] ?? '';
                    $displayPrice = $priceAmount;

                    if ($priceAmount !== '' && is_numeric($priceAmount)) {
                        $displayPrice = '$' . number_format((float)$priceAmount, 0);
                    }

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
                    <div class="col-lg-6 mb-5" data-aos="fade-up" data-aos-delay="<?= (int)$index * 100 ?>">
                        <div class="service-card" id="<?= htmlspecialchars($serviceId) ?>">
                            <div class="service-icon">
                                <i class="bi <?= htmlspecialchars($icon) ?>"></i>
                            </div>
                            <div class="service-content">
                                <h3 class="service-title"><?= htmlspecialchars($service['title'] ?? 'Service') ?></h3>
                                <?php if (!empty($service['description'])): ?>
                                    <p class="service-description">
                                        <?= htmlspecialchars($service['description']) ?>
                                    </p>
                                <?php endif; ?>

                                <?php if (!empty($features)): ?>
                                    <ul class="service-features">
                                        <?php foreach (array_slice($features, 0, 8) as $featureText): ?>
                                            <li><i class="bi bi-check2 text-primary me-2"></i><?= htmlspecialchars($featureText) ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php endif; ?>

                                <?php if (!empty($displayPrice) || !empty($priceLabel)): ?>
                                    <div class="service-pricing">
                                        <?php if (!empty($priceLabel)): ?>
                                            <span class="price-label"><?= htmlspecialchars($priceLabel) ?></span>
                                        <?php endif; ?>
                                        <?php if (!empty($displayPrice)): ?>
                                            <span class="price-amount"><?= htmlspecialchars($displayPrice) ?></span>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12" data-aos="fade-up">
                    <div class="empty-state text-center py-5">
                        <h3 class="mb-3">Services coming soon</h3>
                        <p class="text-muted mb-4">Add services in the admin dashboard to showcase your areas of expertise.</p>
                        <a href="<?= url('contact') ?>" class="btn btn-primary">Start a project</a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Process Section -->
<section class="process-section py-5 bg-light">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto text-center" data-aos="fade-up">
                <h2 class="section-title mb-3"><?= htmlspecialchars($processTitle) ?></h2>
                <?php if (!empty($processSubtitle)): ?>
                    <p class="section-subtitle text-muted mb-5">
                        <?= htmlspecialchars($processSubtitle) ?>
                    </p>
                <?php endif; ?>
            </div>
        </div>
        <div class="row">
            <?php if (!empty($processSteps)): ?>
                <?php foreach ($processSteps as $index => $step): ?>
                    <?php
                    $stepNumber = str_pad((string)($index + 1), 2, '0', STR_PAD_LEFT);
                    $stepIcon = $step['icon_class'] ?? ($step['icon'] ?? 'bi-check-circle');
                    ?>
                    <div class="col-lg-3 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="<?= (int)$index * 100 ?>">
                        <div class="process-step">
                            <div class="process-number"><?= htmlspecialchars($stepNumber) ?></div>
                            <div class="process-icon">
                                <i class="bi <?= htmlspecialchars($stepIcon) ?>"></i>
                            </div>
                            <h4 class="process-title"><?= htmlspecialchars($step['title'] ?? 'Step') ?></h4>
                            <?php if (!empty($step['description'])): ?>
                                <p class="process-description">
                                    <?= htmlspecialchars($step['description']) ?>
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-lg-8 mx-auto" data-aos="fade-up">
                    <div class="empty-state text-center py-5">
                        <h3 class="mb-3">Process outline coming soon</h3>
                        <p class="text-muted">Document your discovery, research, design, and delivery steps to help clients understand how you collaborate.</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Packages Section -->
<section class="packages-section py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto text-center" data-aos="fade-up">
                <h2 class="section-title mb-3"><?= htmlspecialchars($packagesTitle) ?></h2>
                <?php if (!empty($packagesSubtitle)): ?>
                    <p class="section-subtitle text-muted mb-5">
                        <?= htmlspecialchars($packagesSubtitle) ?>
                    </p>
                <?php endif; ?>
            </div>
        </div>
        <div class="row">
            <?php if (!empty($pricingPlans)): ?>
                            <?php foreach ($pricingPlans as $index => $plan): ?>
                    <?php
                                $features = is_array($plan['features_list'] ?? null) ? $plan['features_list'] : [];
                                $isHighlighted = !empty($plan['highlight']);
                                $badgeText = $plan['badge_text'] ?? ($isHighlighted ? 'Recommended' : '');
                                $ctaText = $plan['cta_label'] ?? 'Start Project';
                                $ctaUrlRaw = $plan['cta_url_resolved'] ?? ($plan['cta_url'] ?? '/contact');
                                $ctaUrl = navbar_build_nav_url($ctaUrlRaw);
                                $priceAmountDisplay = $plan['price_display'] ?? '';
                                $pricePeriod = $plan['price_period'] ?? '';
                    ?>
                    <div class="col-lg-4 mb-4" data-aos="fade-up" data-aos-delay="<?= (int)$index * 100 ?>">
                                    <div class="package-card<?= $isHighlighted ? ' package-featured' : '' ?>">
                            <?php if (!empty($badgeText)): ?>
                                <div class="package-badge"><?= htmlspecialchars($badgeText) ?></div>
                            <?php endif; ?>
                            <div class="package-header">
                                            <h3 class="package-name"><?= htmlspecialchars($plan['title'] ?? 'Package') ?></h3>
                                            <?php if (!empty($priceAmountDisplay)): ?>
                                    <div class="package-price">
                                        <?php if (!empty($priceAmountDisplay)): ?>
                                            <span class="price-amount"><?= htmlspecialchars($priceAmountDisplay) ?></span>
                                        <?php endif; ?>
                                        <?php if (!empty($pricePeriod)): ?>
                                            <span class="price-period text-muted">/<?= htmlspecialchars($pricePeriod) ?></span>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                                            <?php if (!empty($plan['subtitle'])): ?>
                                                <p class="package-description"><?= htmlspecialchars($plan['subtitle']) ?></p>
                                <?php endif; ?>
                            </div>
                                        <?php if (!empty($features)): ?>
                                <div class="package-features">
                                    <ul>
                                                    <?php foreach ($features as $feature): ?>
                                            <li><i class="bi bi-check text-primary me-2"></i><?= htmlspecialchars($feature) ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>
                            <div class="package-footer">
                                            <a href="<?= htmlspecialchars($ctaUrl) ?>" class="btn <?= $isHighlighted ? 'btn-primary' : 'btn-outline-primary' ?> w-100">
                                    <?= htmlspecialchars($ctaText) ?>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-lg-8 mx-auto" data-aos="fade-up">
                    <div class="empty-state text-center py-5">
                        <h3 class="mb-3">Packages coming soon</h3>
                        <p class="text-muted">Define pricing tiers to help clients quickly understand how they can engage with you.</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- FAQ Section -->
<section class="faq-section py-5 bg-light">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto text-center" data-aos="fade-up">
                <h2 class="section-title mb-3"><?= htmlspecialchars($faqTitle) ?></h2>
                <?php if (!empty($faqSubtitle)): ?>
                    <p class="section-subtitle text-muted mb-5">
                        <?= htmlspecialchars($faqSubtitle) ?>
                    </p>
                <?php endif; ?>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <?php if (!empty($faqsList)): ?>
                    <div class="accordion" id="faqAccordion">
                        <?php foreach ($faqsList as $index => $faq): ?>
                            <?php
                            $collapseId = 'faq' . ($index + 1);
                            $isFirst = $index === 0;
                            ?>
                            <div class="accordion-item" data-aos="fade-up" data-aos-delay="<?= (int)$index * 100 ?>">
                                <h2 class="accordion-header" id="heading-<?= htmlspecialchars($collapseId) ?>">
                                    <button class="accordion-button<?= $isFirst ? '' : ' collapsed' ?>" type="button" data-bs-toggle="collapse"
                                            data-bs-target="#<?= htmlspecialchars($collapseId) ?>" aria-expanded="<?= $isFirst ? 'true' : 'false' ?>">
                                        <?= htmlspecialchars($faq['question'] ?? 'Question') ?>
                                    </button>
                                </h2>
                                <div id="<?= htmlspecialchars($collapseId) ?>" class="accordion-collapse collapse<?= $isFirst ? ' show' : '' ?>" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body">
                                        <?= nl2br(htmlspecialchars($faq['answer'] ?? '')) ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state text-center py-5" data-aos="fade-up">
                        <h3 class="mb-3">FAQ coming soon</h3>
                        <p class="text-muted">Collect common client questions to set expectations about communication, deliverables, and timelines.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php if (!empty($pageContent)): ?>
<section class="services-extra-content py-5">
    <div class="container" data-aos="fade-up">
        <?= $pageContent ?>
    </div>
</section>
<?php endif; ?>

<?php if (!empty($testimonials)): ?>
<section class="testimonials-section py-5 bg-light">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto text-center" data-aos="fade-up">
                <h2 class="section-title mb-3">What Clients Say</h2>
                <p class="section-subtitle text-muted mb-5">
                    Teams trust the collaborative, research-driven approach I bring to every engagement.
                </p>
            </div>
        </div>
        <div class="row">
            <?php foreach ($testimonials as $index => $testimonial): ?>
                <?php
                $avatar = !empty($testimonial['image_url']) ? $testimonial['image_url'] : asset('images/testimonials/default-avatar.jpg');
                $testimonialText = $testimonial['testimonial_text'] ?? '';
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
        </div>
    </div>
</section>
<?php endif; ?>

<!-- CTA Section -->
<section class="cta-section py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto text-center" data-aos="fade-up">
                <h2 class="cta-title mb-3">Ready to Get Started?</h2>
                <p class="cta-subtitle text-muted mb-4">
                    Let's discuss your project and how I can help you create an exceptional user experience.
                </p>
                <div class="cta-actions">
                    <a href="<?= url('contact') ?>" class="btn btn-primary btn-lg me-3">
                        <i class="bi bi-envelope me-2"></i>
                        Start Your Project
                    </a>
                    <a href="<?= url('portfolio') ?>" class="btn btn-outline-primary btn-lg">
                        <i class="bi bi-collection me-2"></i>
                        View My Work
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>
