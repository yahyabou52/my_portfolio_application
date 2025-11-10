<?php<?php                    I follow a structured approach to ensure every project delivers exceptional results 

$pageSections = $page_sections ?? [];

$pageContent = $page_content ?? '';$pageSections = $page_sections ?? [];                    and meets your business objectives.

$servicesList = $services ?? [];

$processSteps = $process_steps ?? [];$pageContent = $page_content ?? '';                </p>

$pricingPackages = $pricing_packages ?? [];

$faqsList = $faqs ?? [];$servicesList = $services ?? [];            </div>

$testimonials = $featured_testimonials ?? [];--

$processSteps = $process_steps ?? [];        </div>

$heroSection = $pageSections['hero'] ?? [];

$servicesTitle = $heroSection['title'] ?? 'My Services';$pricingPackages = $pricing_packages ?? [];        

$servicesSubtitle = $heroSection['subtitle'] ?? 'I offer comprehensive design solutions that pair human-centered strategy with striking visual systems.';

$faqsList = $faqs ?? [];        <div class="row">

$processSection = $pageSections['process'] ?? [];

$processTitle = $processSection['title'] ?? 'My Design Process';$testimonials = $featured_testimonials ?? [];            <div class="col-lg-3 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="100">

$processSubtitle = $processSection['subtitle'] ?? 'A collaborative, transparent approach keeps teams aligned and projects moving forward.';

                <div class="process-step">

$packagesSection = $pageSections['packages'] ?? [];

$packagesTitle = $packagesSection['title'] ?? 'Service Packages';$heroSection = $pageSections['hero'] ?? [];                    <div class="process-number">01</div>

$packagesSubtitle = $packagesSection['subtitle'] ?? 'Pick the collaboration model that best fits your timeline, scope, and budget.';

$servicesTitle = $heroSection['title'] ?? 'My Services';                    <div class="process-icon">

$faqSection = $pageSections['faq'] ?? [];

$faqTitle = $faqSection['title'] ?? 'Frequently Asked Questions';$servicesSubtitle = $heroSection['subtitle'] ?? 'I offer comprehensive design solutions that pair human-centered strategy with striking visual systems.';                        <i class="bi bi-lightbulb"></i>

$faqSubtitle = $faqSection['subtitle'] ?? 'Common questions about scope, timelines, pricing, and collaboration.';

?>                    </div>



<!-- Services Hero -->$processSection = $pageSections['process'] ?? [];                    <h4 class="process-title">Discovery</h4>

<section class="services-hero py-5 mt-5">

    <div class="container">$processTitle = $processSection['title'] ?? 'My Design Process';                    <p class="process-description">

        <div class="row">

            <div class="col-lg-8 mx-auto text-center" data-aos="fade-up">$processSubtitle = $processSection['subtitle'] ?? 'A collaborative, transparent approach keeps teams aligned and projects moving forward.';                        Understanding your business goals, target audience, and project requirements 

                <h1 class="services-title mb-4"><?= htmlspecialchars($servicesTitle) ?></h1>

                <?php if (!empty($servicesSubtitle)): ?>                        through detailed discussions and research.

                    <p class="services-subtitle text-muted mb-5">

                        <?= htmlspecialchars($servicesSubtitle) ?>$packagesSection = $pageSections['packages'] ?? [];                    </p>

                    </p>

                <?php endif; ?>$packagesTitle = $packagesSection['title'] ?? 'Service Packages';                </div>

            </div>

        </div>$packagesSubtitle = $packagesSection['subtitle'] ?? 'Pick the collaboration model that best fits your timeline, scope, and budget.';            </div>

    </div>

</section>            



<!-- Main Services -->$faqSection = $pageSections['faq'] ?? [];            <div class="col-lg-3 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="200">

<section class="main-services py-5">

    <div class="container">$faqTitle = $faqSection['title'] ?? 'Frequently Asked Questions';                <div class="process-step">

        <div class="row">

            <?php if (!empty($servicesList)): ?>$faqSubtitle = $faqSection['subtitle'] ?? 'Common questions about scope, timelines, pricing, and collaboration.';                    <div class="process-number">02</div>

                <?php foreach ($servicesList as $index => $service): ?>

                    <?php?>                    <div class="process-icon">

                    $serviceId = 'service-' . ($service['id'] ?? $index);

                    $icon = $service['icon'] ?? 'bi-lightning-charge';                        <i class="bi bi-search"></i>

                    $priceLabel = $service['price_label'] ?? 'Starting from';

                    $priceAmount = $service['price_amount'] ?? '';<!-- Services Hero -->                    </div>

                    $displayPrice = $priceAmount;

                    if ($priceAmount !== '' && is_numeric($priceAmount)) {<section class="services-hero py-5 mt-5">                    <h4 class="process-title">Research</h4>

                        $displayPrice = '$' . number_format((float)$priceAmount, 0);

                    }    <div class="container">                    <p class="process-description">

                    $featuresRaw = $service['features'] ?? [];

                    $features = [];        <div class="row">                        Conducting user research, competitive analysis, and market research to 

                    if (is_array($featuresRaw)) {

                        foreach ($featuresRaw as $feature) {            <div class="col-lg-8 mx-auto text-center" data-aos="fade-up">                        inform design decisions and strategy.

                            if (is_array($feature) && !empty($feature['feature_text'])) {

                                $features[] = $feature['feature_text'];                <h1 class="services-title mb-4"><?= htmlspecialchars($servicesTitle) ?></h1>                    </p>

                            } elseif (is_string($feature)) {

                                $features[] = $feature;                <?php if (!empty($servicesSubtitle)): ?>                </div>

                            }

                        }                    <p class="services-subtitle text-muted mb-5">            </div>

                    }

                    ?>                        <?= htmlspecialchars($servicesSubtitle) ?>            

                    <div class="col-lg-6 mb-5" data-aos="fade-up" data-aos-delay="<?= (int)$index * 100 ?>">

                        <div class="service-card" id="<?= htmlspecialchars($serviceId) ?>">                    </p>            <div class="col-lg-3 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="300">

                            <div class="service-icon">

                                <i class="bi <?= htmlspecialchars($icon) ?>"></i>                <?php endif; ?>                <div class="process-step">

                            </div>

                            <div class="service-content">            </div>                    <div class="process-number">03</div>

                                <h3 class="service-title"><?= htmlspecialchars($service['title'] ?? 'Service') ?></h3>

                                <?php if (!empty($service['description'])): ?>        </div>                    <div class="process-icon">

                                    <p class="service-description">

                                        <?= htmlspecialchars($service['description']) ?>    </div>                        <i class="bi bi-pencil"></i>

                                    </p>

                                <?php endif; ?></section>                    </div>

                                <?php if (!empty($features)): ?>

                                    <ul class="service-features">                    <h4 class="process-title">Design</h4>

                                        <?php foreach (array_slice($features, 0, 8) as $featureText): ?>

                                            <li><i class="bi bi-check2 text-primary me-2"></i><?= htmlspecialchars($featureText) ?></li><!-- Main Services -->                    <p class="process-description">

                                        <?php endforeach; ?>

                                    </ul><section class="main-services py-5">                        Creating wireframes, prototypes, and high-fidelity designs based on 

                                <?php endif; ?>

                                <?php if (!empty($displayPrice) || !empty($priceLabel)): ?>    <div class="container">                        research insights and user needs.

                                    <div class="service-pricing">

                                        <?php if (!empty($priceLabel)): ?>        <div class="row">                    </p>

                                            <span class="price-label"><?= htmlspecialchars($priceLabel) ?></span>

                                        <?php endif; ?>            <?php if (!empty($servicesList)): ?>                </div>

                                        <?php if (!empty($displayPrice)): ?>

                                            <span class="price-amount"><?= htmlspecialchars($displayPrice) ?></span>                <?php foreach ($servicesList as $index => $service): ?>            </div>

                                        <?php endif; ?>

                                    </div>                    <?php            

                                <?php endif; ?>

                            </div>                    $serviceId = 'service-' . ($service['id'] ?? $index);            <div class="col-lg-3 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="400">

                        </div>

                    </div>                    $icon = $service['icon'] ?? 'bi-lightning-charge';                <div class="process-step">

                <?php endforeach; ?>

            <?php else: ?>                    $priceLabel = $service['price_label'] ?? 'Starting from';                    <div class="process-number">04</div>

                <div class="col-12" data-aos="fade-up">

                    <div class="empty-state text-center py-5">                    $priceAmount = $service['price_amount'] ?? '';                    <div class="process-icon">

                        <h3 class="mb-3">Services coming soon</h3>

                        <p class="text-muted mb-4">Add services in the admin dashboard to showcase your areas of expertise.</p>                    $featuresRaw = $service['features'] ?? [];                        <i class="bi bi-check-circle"></i>

                        <a href="<?= url('contact') ?>" class="btn btn-primary">Start a project</a>

                    </div>                    $features = [];                    </div>

                </div>

            <?php endif; ?>                    if (is_array($featuresRaw)) {                    <h4 class="process-title">Deliver</h4>

        </div>

    </div>                        foreach ($featuresRaw as $feature) {                    <p class="process-description">

</section>

                            if (is_array($feature) && !empty($feature['feature_text'])) {                        Testing, refining, and delivering the final design assets along with 

<!-- Process Section -->

<section class="process-section py-5 bg-light">                                $features[] = $feature['feature_text'];                        documentation and ongoing support.

    <div class="container">

        <div class="row">                            } elseif (is_string($feature)) {                    </p>

            <div class="col-lg-8 mx-auto text-center" data-aos="fade-up">

                <h2 class="section-title mb-3"><?= htmlspecialchars($processTitle) ?></h2>                                $features[] = $feature;                </div>

                <?php if (!empty($processSubtitle)): ?>

                    <p class="section-subtitle text-muted mb-5">                            }            </div>

                        <?= htmlspecialchars($processSubtitle) ?>

                    </p>                        }        </div>

                <?php endif; ?>

            </div>                    }    </div>

        </div>

                    ?></section>

        <div class="row">

            <?php if (!empty($processSteps)): ?>                    <div class="col-lg-6 mb-5" data-aos="fade-up" data-aos-delay="<?= (int)$index * 100 ?>">

                <?php foreach ($processSteps as $index => $step): ?>

                    <?php                        <div class="service-card" id="<?= htmlspecialchars($serviceId) ?>"><!-- Packages Section -->

                    $stepNumber = str_pad((string)($index + 1), 2, '0', STR_PAD_LEFT);

                    $stepIcon = $step['icon'] ?? 'bi-check-circle';                            <div class="service-icon"><section class="packages-section py-5">

                    ?>

                    <div class="col-lg-3 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="<?= (int)$index * 100 ?>">                                <i class="bi <?= htmlspecialchars($icon) ?>"></i>    <div class="container">

                        <div class="process-step">

                            <div class="process-number"><?= htmlspecialchars($stepNumber) ?></div>                            </div>        <div class="row">

                            <div class="process-icon">

                                <i class="bi <?= htmlspecialchars($stepIcon) ?>"></i>                            <div class="service-content">            <div class="col-lg-8 mx-auto text-center" data-aos="fade-up">

                            </div>

                            <h4 class="process-title"><?= htmlspecialchars($step['title'] ?? 'Step') ?></h4>                                <h3 class="service-title"><?= htmlspecialchars($service['title'] ?? 'Service') ?></h3>                <h2 class="section-title mb-3">Service Packages</h2>

                            <?php if (!empty($step['description'])): ?>

                                <p class="process-description">                                <?php if (!empty($service['description'])): ?>                <p class="section-subtitle text-muted mb-5">

                                    <?= htmlspecialchars($step['description']) ?>

                                </p>                                    <p class="service-description">                    Choose the package that best fits your project needs and budget.

                            <?php endif; ?>

                        </div>                                        <?= htmlspecialchars($service['description']) ?>                </p>

                    </div>

                <?php endforeach; ?>                                    </p>            </div>

            <?php else: ?>

                <div class="col-lg-8 mx-auto" data-aos="fade-up">                                <?php endif; ?>        </div>

                    <div class="empty-state text-center py-5">

                        <h3 class="mb-3">Process outline coming soon</h3>                                <?php if (!empty($features)): ?>        

                        <p class="text-muted">Document your discovery, research, design, and delivery steps to help clients understand how you collaborate.</p>

                    </div>                                    <ul class="service-features">        <div class="row">

                </div>

            <?php endif; ?>                                        <?php foreach (array_slice($features, 0, 8) as $featureText): ?>            <!-- Basic Package -->

        </div>

    </div>                                            <li><i class="bi bi-check2 text-primary me-2"></i><?= htmlspecialchars($featureText) ?></li>            <div class="col-lg-4 mb-4" data-aos="fade-up" data-aos-delay="100">

</section>

                                        <?php endforeach; ?>                <div class="package-card">

<!-- Packages Section -->

<section class="packages-section py-5">                                    </ul>                    <div class="package-header">

    <div class="container">

        <div class="row">                                <?php endif; ?>                        <h3 class="package-name">Starter</h3>

            <div class="col-lg-8 mx-auto text-center" data-aos="fade-up">

                <h2 class="section-title mb-3"><?= htmlspecialchars($packagesTitle) ?></h2>                                <?php if (!empty($priceAmount) || !empty($priceLabel)): ?>                        <div class="package-price">

                <?php if (!empty($packagesSubtitle)): ?>

                    <p class="section-subtitle text-muted mb-5">                                    <div class="service-pricing">                            <span class="price-currency">$</span>

                        <?= htmlspecialchars($packagesSubtitle) ?>

                    </p>                                        <?php if (!empty($priceLabel)): ?>                            <span class="price-amount">1,500</span>

                <?php endif; ?>

            </div>                                            <span class="price-label"><?= htmlspecialchars($priceLabel) ?></span>                        </div>

        </div>

                                        <?php endif; ?>                        <p class="package-description">Perfect for small projects and startups</p>

        <div class="row">

            <?php if (!empty($pricingPackages)): ?>                                        <?php if (!empty($priceAmount)): ?>                    </div>

                <?php foreach ($pricingPackages as $index => $package): ?>

                    <?php                                            <span class="price-amount"><?= htmlspecialchars($priceAmount) ?></span>                    <div class="package-features">

                    $packageFeatures = [];

                    if (!empty($package['features']) && is_array($package['features'])) {                                        <?php endif; ?>                        <ul>

                        foreach ($package['features'] as $feature) {

                            if (is_array($feature) && !empty($feature['feature_text'])) {                                    </div>                            <li><i class="bi bi-check text-primary me-2"></i>5 Page designs</li>

                                $packageFeatures[] = $feature['feature_text'];

                            }                                <?php endif; ?>                            <li><i class="bi bi-check text-primary me-2"></i>Mobile responsive</li>

                        }

                    }                            </div>                            <li><i class="bi bi-check text-primary me-2"></i>Basic wireframes</li>

                    $isFeatured = !empty($package['is_featured']);

                    $badgeText = $package['badge_text'] ?? ($isFeatured ? 'Popular' : '');                        </div>                            <li><i class="bi bi-check text-primary me-2"></i>2 Revisions</li>

                    $ctaText = $package['cta_text'] ?? 'Start Project';

                    $ctaUrl = navbar_build_nav_url($package['cta_url'] ?? '/contact');                    </div>                            <li><i class="bi bi-check text-primary me-2"></i>1 Week delivery</li>

                    $priceAmount = $package['price_amount'] ?? '';

                    $priceAmountDisplay = $priceAmount;                <?php endforeach; ?>                        </ul>

                    if ($priceAmount !== '' && is_numeric($priceAmount)) {

                        $priceAmountDisplay = '$' . number_format((float)$priceAmount, 0);            <?php else: ?>                    </div>

                    }

                    $priceLabel = $package['price_label'] ?? '';                <div class="col-12" data-aos="fade-up">                    <div class="package-footer">

                    $pricePeriod = $package['price_period'] ?? '';

                    ?>                    <div class="empty-state text-center py-5">                        <a href="<?= url('contact') ?>" class="btn btn-outline-primary w-100">

                    <div class="col-lg-4 mb-4" data-aos="fade-up" data-aos-delay="<?= (int)$index * 100 ?>">

                        <div class="package-card<?= $isFeatured ? ' package-featured' : '' ?>">                        <h3 class="mb-3">Services coming soon</h3>                            Choose Starter

                            <?php if (!empty($badgeText)): ?>

                                <div class="package-badge"><?= htmlspecialchars($badgeText) ?></div>                        <p class="text-muted mb-4">Add services in the admin dashboard to showcase your areas of expertise.</p>                        </a>

                            <?php endif; ?>

                            <div class="package-header">                        <a href="<?= url('contact') ?>" class="btn btn-primary">Start a project</a>                    </div>

                                <h3 class="package-name"><?= htmlspecialchars($package['name'] ?? 'Package') ?></h3>

                                <?php if (!empty($priceAmountDisplay) || !empty($priceLabel)): ?>                    </div>                </div>

                                    <div class="package-price">

                                        <?php if (!empty($priceLabel)): ?>                </div>            </div>

                                            <span class="price-label d-block text-muted small mb-1"><?= htmlspecialchars($priceLabel) ?></span>

                                        <?php endif; ?>            <?php endif; ?>            

                                        <?php if (!empty($priceAmountDisplay)): ?>

                                            <span class="price-amount"><?= htmlspecialchars($priceAmountDisplay) ?></span>        </div>            <!-- Professional Package -->

                                        <?php endif; ?>

                                        <?php if (!empty($pricePeriod)): ?>    </div>            <div class="col-lg-4 mb-4" data-aos="fade-up" data-aos-delay="200">

                                            <span class="price-period text-muted">/<?= htmlspecialchars($pricePeriod) ?></span>

                                        <?php endif; ?></section>                <div class="package-card package-featured">

                                    </div>

                                <?php endif; ?>                    <div class="package-badge">Most Popular</div>

                                <?php if (!empty($package['description'])): ?>

                                    <p class="package-description"><?= htmlspecialchars($package['description']) ?></p><!-- Process Section -->                    <div class="package-header">

                                <?php endif; ?>

                            </div><section class="process-section py-5 bg-light">                        <h3 class="package-name">Professional</h3>

                            <?php if (!empty($packageFeatures)): ?>

                                <div class="package-features">    <div class="container">                        <div class="package-price">

                                    <ul>

                                        <?php foreach ($packageFeatures as $feature): ?>        <div class="row">                            <span class="price-currency">$</span>

                                            <li><i class="bi bi-check text-primary me-2"></i><?= htmlspecialchars($feature) ?></li>

                                        <?php endforeach; ?>            <div class="col-lg-8 mx-auto text-center" data-aos="fade-up">                            <span class="price-amount">3,500</span>

                                    </ul>

                                </div>                <h2 class="section-title mb-3"><?= htmlspecialchars($processTitle) ?></h2>                        </div>

                            <?php endif; ?>

                            <div class="package-footer">                <?php if (!empty($processSubtitle)): ?>                        <p class="package-description">Ideal for established businesses</p>

                                <a href="<?= htmlspecialchars($ctaUrl) ?>" class="btn <?= $isFeatured ? 'btn-primary' : 'btn-outline-primary' ?> w-100">

                                    <?= htmlspecialchars($ctaText) ?>                    <p class="section-subtitle text-muted mb-5">                    </div>

                                </a>

                            </div>                        <?= htmlspecialchars($processSubtitle) ?>                    <div class="package-features">

                        </div>

                    </div>                    </p>                        <ul>

                <?php endforeach; ?>

            <?php else: ?>                <?php endif; ?>                            <li><i class="bi bi-check text-primary me-2"></i>15 Page designs</li>

                <div class="col-lg-8 mx-auto" data-aos="fade-up">

                    <div class="empty-state text-center py-5">            </div>                            <li><i class="bi bi-check text-primary me-2"></i>Mobile responsive</li>

                        <h3 class="mb-3">Packages coming soon</h3>

                        <p class="text-muted">Define pricing tiers to help clients quickly understand how they can engage with you.</p>        </div>                            <li><i class="bi bi-check text-primary me-2"></i>Interactive prototypes</li>

                    </div>

                </div>                            <li><i class="bi bi-check text-primary me-2"></i>User research</li>

            <?php endif; ?>

        </div>        <div class="row">                            <li><i class="bi bi-check text-primary me-2"></i>Design system</li>

    </div>

</section>            <?php if (!empty($processSteps)): ?>                            <li><i class="bi bi-check text-primary me-2"></i>5 Revisions</li>



<!-- FAQ Section -->                <?php foreach ($processSteps as $index => $step): ?>                            <li><i class="bi bi-check text-primary me-2"></i>3 Week delivery</li>

<section class="faq-section py-5 bg-light">

    <div class="container">                    <?php                        </ul>

        <div class="row">

            <div class="col-lg-8 mx-auto text-center" data-aos="fade-up">                    $stepNumber = str_pad((string)($index + 1), 2, '0', STR_PAD_LEFT);                    </div>

                <h2 class="section-title mb-3"><?= htmlspecialchars($faqTitle) ?></h2>

                <?php if (!empty($faqSubtitle)): ?>                    $stepIcon = $step['icon'] ?? 'bi-check-circle';                    <div class="package-footer">

                    <p class="section-subtitle text-muted mb-5">

                        <?= htmlspecialchars($faqSubtitle) ?>                    ?>                        <a href="<?= url('contact') ?>" class="btn btn-primary w-100">

                    </p>

                <?php endif; ?>                    <div class="col-lg-3 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="<?= (int)$index * 100 ?>">                            Choose Professional

            </div>

        </div>                        <div class="process-step">                        </a>



        <div class="row">                            <div class="process-number"><?= htmlspecialchars($stepNumber) ?></div>                    </div>

            <div class="col-lg-8 mx-auto">

                <?php if (!empty($faqsList)): ?>                            <div class="process-icon">                </div>

                    <div class="accordion" id="faqAccordion">

                        <?php foreach ($faqsList as $index => $faq): ?>                                <i class="bi <?= htmlspecialchars($stepIcon) ?>"></i>            </div>

                            <?php

                                $collapseId = 'faq' . ($index + 1);                            </div>            

                                $isFirst = $index === 0;

                            ?>                            <h4 class="process-title"><?= htmlspecialchars($step['title'] ?? 'Step') ?></h4>            <!-- Enterprise Package -->

                            <div class="accordion-item" data-aos="fade-up" data-aos-delay="<?= (int)$index * 100 ?>">

                                <h2 class="accordion-header" id="heading-<?= htmlspecialchars($collapseId) ?>">                            <?php if (!empty($step['description'])): ?>            <div class="col-lg-4 mb-4" data-aos="fade-up" data-aos-delay="300">

                                    <button class="accordion-button<?= $isFirst ? '' : ' collapsed' ?>" type="button" data-bs-toggle="collapse"

                                            data-bs-target="#<?= htmlspecialchars($collapseId) ?>" aria-expanded="<?= $isFirst ? 'true' : 'false' ?>">                                <p class="process-description">                <div class="package-card">

                                        <?= htmlspecialchars($faq['question'] ?? 'Question') ?>

                                    </button>                                    <?= htmlspecialchars($step['description']) ?>                    <div class="package-header">

                                </h2>

                                <div id="<?= htmlspecialchars($collapseId) ?>" class="accordion-collapse collapse<?= $isFirst ? ' show' : '' ?>" data-bs-parent="#faqAccordion">                                </p>                        <h3 class="package-name">Enterprise</h3>

                                    <div class="accordion-body">

                                        <?= nl2br(htmlspecialchars($faq['answer'] ?? '')) ?>                            <?php endif; ?>                        <div class="package-price">

                                    </div>

                                </div>                        </div>                            <span class="price-currency">$</span>

                            </div>

                        <?php endforeach; ?>                    </div>                            <span class="price-amount">7,500</span>

                    </div>

                <?php else: ?>                <?php endforeach; ?>                        </div>

                    <div class="empty-state text-center py-5" data-aos="fade-up">

                        <h3 class="mb-3">FAQ coming soon</h3>            <?php else: ?>                        <p class="package-description">Complete solution for large projects</p>

                        <p class="text-muted">Collect common client questions to set expectations about communication, deliverables, and timelines.</p>

                    </div>                <div class="col-lg-8 mx-auto" data-aos="fade-up">                    </div>

                <?php endif; ?>

            </div>                    <div class="empty-state text-center py-5">                    <div class="package-features">

        </div>

    </div>                        <h3 class="mb-3">Process outline coming soon</h3>                        <ul>

</section>

                        <p class="text-muted">Document your discovery, research, design, and delivery steps to help clients understand how you collaborate.</p>                            <li><i class="bi bi-check text-primary me-2"></i>Unlimited pages</li>

<?php if (!empty($pageContent)): ?>

<section class="services-extra-content py-5">                    </div>                            <li><i class="bi bi-check text-primary me-2"></i>Multi-platform design</li>

    <div class="container" data-aos="fade-up">

        <?= $pageContent ?>                </div>                            <li><i class="bi bi-check text-primary me-2"></i>Advanced prototypes</li>

    </div>

</section>            <?php endif; ?>                            <li><i class="bi bi-check text-primary me-2"></i>Comprehensive research</li>

<?php endif; ?>

        </div>                            <li><i class="bi bi-check text-primary me-2"></i>Complete design system</li>

<?php if (!empty($testimonials)): ?>

<section class="testimonials-section py-5 bg-light">    </div>                            <li><i class="bi bi-check text-primary me-2"></i>Brand identity</li>

    <div class="container">

        <div class="row"></section>                            <li><i class="bi bi-check text-primary me-2"></i>Unlimited revisions</li>

            <div class="col-lg-8 mx-auto text-center" data-aos="fade-up">

                <h2 class="section-title mb-3">What Clients Say</h2>                            <li><i class="bi bi-check text-primary me-2"></i>6 Week delivery</li>

                <p class="section-subtitle text-muted mb-5">

                    Teams trust the collaborative, research-driven approach I bring to every engagement.<!-- Packages Section -->                        </ul>

                </p>

            </div><section class="packages-section py-5">                    </div>

        </div>

        <div class="row">    <div class="container">                    <div class="package-footer">

            <?php foreach ($testimonials as $index => $testimonial): ?>

                <?php        <div class="row">                        <a href="<?= url('contact') ?>" class="btn btn-outline-primary w-100">

                $avatar = !empty($testimonial['image_url']) ? $testimonial['image_url'] : asset('images/testimonials/default-avatar.jpg');

                $testimonialText = $testimonial['testimonial_text'] ?? '';            <div class="col-lg-8 mx-auto text-center" data-aos="fade-up">                            Choose Enterprise

                $rating = (int)($testimonial['rating'] ?? 5);

                ?>                <h2 class="section-title mb-3"><?= htmlspecialchars($packagesTitle) ?></h2>                        </a>

                <div class="col-lg-4 mb-4" data-aos="fade-up" data-aos-delay="<?= (int)$index * 100 ?>">

                    <div class="testimonial-card">                <?php if (!empty($packagesSubtitle)): ?>                    </div>

                        <div class="testimonial-content">

                            <?php if ($rating > 0): ?>                    <p class="section-subtitle text-muted mb-5">                </div>

                                <div class="testimonial-stars mb-3">

                                    <?php for ($i = 0; $i < 5; $i++): ?>                        <?= htmlspecialchars($packagesSubtitle) ?>            </div>

                                        <i class="bi <?= $i < $rating ? 'bi-star-fill text-warning' : 'bi-star text-warning' ?>"></i>

                                    <?php endfor; ?>                    </p>        </div>

                                </div>

                            <?php endif; ?>                <?php endif; ?>    </div>

                            <p class="testimonial-text">

                                "<?= htmlspecialchars($testimonialText) ?>"            </div></section>

                            </p>

                        </div>        </div>

                        <div class="testimonial-author">

                            <img src="<?= htmlspecialchars($avatar) ?>" alt="<?= htmlspecialchars($testimonial['client_name'] ?? 'Client') ?>"<!-- FAQ Section -->

                                 class="testimonial-avatar">

                            <div class="author-info">        <div class="row"><section class="faq-section py-5 bg-light">

                                <h6 class="author-name"><?= htmlspecialchars($testimonial['client_name'] ?? 'Client Name') ?></h6>

                                <p class="author-title">            <?php if (!empty($pricingPackages)): ?>    <div class="container">

                                    <?= htmlspecialchars(trim(($testimonial['client_position'] ?? '') . ' ' . ($testimonial['client_company'] ?? ''))) ?>

                                </p>                <?php foreach ($pricingPackages as $index => $package): ?>        <div class="row">

                            </div>

                        </div>                    <?php            <div class="col-lg-8 mx-auto text-center" data-aos="fade-up">

                    </div>

                </div>                    $packageFeatures = [];                <h2 class="section-title mb-3">Frequently Asked Questions</h2>

            <?php endforeach; ?>

        </div>                    if (!empty($package['features']) && is_array($package['features'])) {                <p class="section-subtitle text-muted mb-5">

    </div>

</section>                        foreach ($package['features'] as $feature) {                    Here are answers to some common questions about my services and process.

<?php endif; ?>

                            if (is_array($feature) && !empty($feature['feature_text'])) {                </p>

<!-- CTA Section -->

<section class="cta-section py-5">                                $packageFeatures[] = $feature['feature_text'];            </div>

    <div class="container">

        <div class="row">                            }        </div>

            <div class="col-lg-8 mx-auto text-center" data-aos="fade-up">

                <h2 class="cta-title mb-3">Ready to Get Started?</h2>                        }        

                <p class="cta-subtitle text-muted mb-4">

                    Let's discuss your project and how I can help you create an exceptional user experience.                    }        <div class="row">

                </p>

                <div class="cta-actions">                    $isFeatured = !empty($package['is_featured']);            <div class="col-lg-8 mx-auto">

                    <a href="<?= url('contact') ?>" class="btn btn-primary btn-lg me-3">

                        <i class="bi bi-envelope me-2"></i>                    $badgeText = $package['badge_text'] ?? ($isFeatured ? 'Popular' : '');                <div class="accordion" id="faqAccordion">

                        Start Your Project

                    </a>                    $ctaText = $package['cta_text'] ?? 'Start Project';                    <div class="accordion-item" data-aos="fade-up" data-aos-delay="100">

                    <a href="<?= url('portfolio') ?>" class="btn btn-outline-primary btn-lg">

                        <i class="bi bi-collection me-2"></i>                    $ctaUrl = navbar_build_nav_url($package['cta_url'] ?? '/contact');                        <h2 class="accordion-header">

                        View My Work

                    </a>                    $priceAmount = $package['price_amount'] ?? '';                            <button class="accordion-button" type="button" data-bs-toggle="collapse" 

                </div>

            </div>                    $priceLabel = $package['price_label'] ?? '';                                    data-bs-target="#faq1" aria-expanded="true">

        </div>

    </div>                    $pricePeriod = $package['price_period'] ?? '';                                What's included in your UI/UX design service?

</section>

                    ?>                            </button>

                    <div class="col-lg-4 mb-4" data-aos="fade-up" data-aos-delay="<?= (int)$index * 100 ?>">                        </h2>

                        <div class="package-card<?= $isFeatured ? ' package-featured' : '' ?>">                        <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">

                            <?php if (!empty($badgeText)): ?>                            <div class="accordion-body">

                                <div class="package-badge"><?= htmlspecialchars($badgeText) ?></div>                                My UI/UX design service includes user research, wireframing, high-fidelity mockups, 

                            <?php endif; ?>                                interactive prototypes, and design documentation. I also provide design assets in 

                            <div class="package-header">                                various formats for development handoff.

                                <h3 class="package-name"><?= htmlspecialchars($package['name'] ?? 'Package') ?></h3>                            </div>

                                <?php if (!empty($priceAmount) || !empty($priceLabel)): ?>                        </div>

                                    <div class="package-price">                    </div>

                                        <?php if (!empty($priceLabel)): ?>                    

                                            <span class="price-label d-block text-muted small mb-1"><?= htmlspecialchars($priceLabel) ?></span>                    <div class="accordion-item" data-aos="fade-up" data-aos-delay="200">

                                        <?php endif; ?>                        <h2 class="accordion-header">

                                        <?php if (!empty($priceAmount)): ?>                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" 

                                            <span class="price-amount"><?= htmlspecialchars($priceAmount) ?></span>                                    data-bs-target="#faq2" aria-expanded="false">

                                        <?php endif; ?>                                How long does a typical project take?

                                        <?php if (!empty($pricePeriod)): ?>                            </button>

                                            <span class="price-period text-muted">/<?= htmlspecialchars($pricePeriod) ?></span>                        </h2>

                                        <?php endif; ?>                        <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">

                                    </div>                            <div class="accordion-body">

                                <?php endif; ?>                                Project timelines vary based on scope and complexity. A simple website design 

                                <?php if (!empty($package['description'])): ?>                                might take 1-2 weeks, while a comprehensive mobile app design could take 4-6 weeks. 

                                    <p class="package-description"><?= htmlspecialchars($package['description']) ?></p>                                I'll provide a detailed timeline during our initial consultation.

                                <?php endif; ?>                            </div>

                            </div>                        </div>

                            <?php if (!empty($packageFeatures)): ?>                    </div>

                                <div class="package-features">                    

                                    <ul>                    <div class="accordion-item" data-aos="fade-up" data-aos-delay="300">

                                        <?php foreach ($packageFeatures as $feature): ?>                        <h2 class="accordion-header">

                                            <li><i class="bi bi-check text-primary me-2"></i><?= htmlspecialchars($feature) ?></li>                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" 

                                        <?php endforeach; ?>                                    data-bs-target="#faq3" aria-expanded="false">

                                    </ul>                                Do you work with development teams?

                                </div>                            </button>

                            <?php endif; ?>                        </h2>

                            <div class="package-footer">                        <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">

                                <a href="<?= htmlspecialchars($ctaUrl) ?>" class="btn <?= $isFeatured ? 'btn-primary' : 'btn-outline-primary' ?> w-100">                            <div class="accordion-body">

                                    <?= htmlspecialchars($ctaText) ?>                                Yes, I regularly collaborate with development teams to ensure designs are 

                                </a>                                implemented correctly. I provide detailed specifications, assets, and am 

                            </div>                                available for questions throughout the development process.

                        </div>                            </div>

                    </div>                        </div>

                <?php endforeach; ?>                    </div>

            <?php else: ?>                    

                <div class="col-lg-8 mx-auto" data-aos="fade-up">                    <div class="accordion-item" data-aos="fade-up" data-aos-delay="400">

                    <div class="empty-state text-center py-5">                        <h2 class="accordion-header">

                        <h3 class="mb-3">Packages coming soon</h3>                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" 

                        <p class="text-muted">Define pricing tiers to help clients quickly understand how they can engage with you.</p>                                    data-bs-target="#faq4" aria-expanded="false">

                    </div>                                What's your revision policy?

                </div>                            </button>

            <?php endif; ?>                        </h2>

        </div>                        <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">

    </div>                            <div class="accordion-body">

</section>                                Each package includes a specific number of revisions. Additional revisions 

                                can be purchased if needed. I encourage feedback throughout the design process 

<!-- FAQ Section -->                                to minimize major changes at the end.

<section class="faq-section py-5 bg-light">                            </div>

    <div class="container">                        </div>

        <div class="row">                    </div>

            <div class="col-lg-8 mx-auto text-center" data-aos="fade-up">                </div>

                <h2 class="section-title mb-3"><?= htmlspecialchars($faqTitle) ?></h2>            </div>

                <?php if (!empty($faqSubtitle)): ?>        </div>

                    <p class="section-subtitle text-muted mb-5">    </div>

                        <?= htmlspecialchars($faqSubtitle) ?></section>

                    </p>

                <?php endif; ?><!-- CTA Section -->

            </div><section class="cta-section py-5">

        </div>    <div class="container">

        <div class="row">

        <div class="row">            <div class="col-lg-8 mx-auto text-center" data-aos="fade-up">

            <div class="col-lg-8 mx-auto">                <h2 class="cta-title mb-3">Ready to Get Started?</h2>

                <?php if (!empty($faqsList)): ?>                <p class="cta-subtitle text-muted mb-4">

                    <div class="accordion" id="faqAccordion">                    Let's discuss your project and how I can help you create an exceptional user experience.

                        <?php foreach ($faqsList as $index => $faq): ?>                </p>

                            <?php                <div class="cta-actions">

                            $collapseId = 'faq' . ($index + 1);                    <a href="<?= url('contact') ?>" class="btn btn-primary btn-lg me-3">

                            $isFirst = $index === 0;                        <i class="bi bi-envelope me-2"></i>

                            ?>                        Start Your Project

                            <div class="accordion-item" data-aos="fade-up" data-aos-delay="<?= (int)$index * 100 ?>">                    </a>

                                <h2 class="accordion-header" id="heading-<?= htmlspecialchars($collapseId) ?>">                    <a href="<?= url('portfolio') ?>" class="btn btn-outline-primary btn-lg">

                                    <button class="accordion-button<?= $isFirst ? '' : ' collapsed' ?>" type="button" data-bs-toggle="collapse"                        <i class="bi bi-collection me-2"></i>

                                            data-bs-target="#<?= htmlspecialchars($collapseId) ?>" aria-expanded="<?= $isFirst ? 'true' : 'false' ?>">                        View My Work

                                        <?= htmlspecialchars($faq['question'] ?? 'Question') ?>                    </a>

                                    </button>                </div>

                                </h2>            </div>

                                <div id="<?= htmlspecialchars($collapseId) ?>" class="accordion-collapse collapse<?= $isFirst ? ' show' : '' ?>" data-bs-parent="#faqAccordion">        </div>

                                    <div class="accordion-body">    </div>

                                        <?= nl2br(htmlspecialchars($faq['answer'] ?? '')) ?></section>
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
