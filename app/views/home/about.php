<?php
$aboutData = $about ?? [];
$pageSections = $page_sections ?? [];
$pageContent = $page_content ?? '';
$skillsByCategory = $skills_by_category ?? [];
$testimonials = $featured_testimonials ?? [];

$greeting = trim((string)($aboutData['greeting'] ?? $aboutData['about_subtitle'] ?? ''));
$headline = $aboutData['headline'] ?? $aboutData['about_title'] ?? 'About Me';
$bio = $aboutData['bio'] ?? $aboutData['about_content'] ?? '';
$philosophy = $aboutData['philosophy'] ?? '';
$aboutImage = !empty($aboutData['image_url'] ?? null)
    ? $aboutData['image_url']
    : (!empty($aboutData['about_image_url']) ? $aboutData['about_image_url'] : asset('images/placeholder.svg'));
$aboutImageAlt = trim((string)($aboutData['image_alt'] ?? $aboutData['about_image_alt'] ?? $headline));

$timelineItems = $aboutData['timeline'] ?? ($pageSections['timeline']['items'] ?? []);
$timelineMeta = $aboutData['timeline_meta'] ?? [];
$timelineTitle = $timelineMeta['title'] ?? ($pageSections['timeline']['title'] ?? 'My Journey');
$timelineSubtitle = $timelineMeta['subtitle'] ?? ($pageSections['timeline']['subtitle'] ?? '');

$timelineGroups = [
    'experience' => [
        'label' => 'Experience',
        'items' => []
    ],
    'education' => [
        'label' => 'Education',
        'items' => []
    ]
];

foreach ($timelineItems as $timelineItem) {
    $groupKey = !empty($timelineItem['is_education']) ? 'education' : 'experience';
    $timelineGroups[$groupKey]['items'][] = $timelineItem;
}

$hasTimelineContent = !empty($timelineGroups['experience']['items']) || !empty($timelineGroups['education']['items']);
?>

<!-- About Hero Section -->
<?php if (WebsiteContent::visible('about.section.about-hero')): ?><section data-section-key="about.section.about-hero" class="about-hero py-5 mt-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-5 col-xl-4 mb-4 mb-lg-0" data-aos="fade-right">
                <div class="about-image shadow-sm rounded-4 overflow-hidden">
                    <img src="<?= htmlspecialchars($aboutImage) ?>"
                         alt="<?= htmlspecialchars($aboutImageAlt !== '' ? $aboutImageAlt : $headline) ?>"
                         class="img-fluid w-100">
                </div>
            </div>
            <div class="col-lg-7 col-xl-8" data-aos="fade-left" data-aos-delay="150">
                <div class="about-content ps-lg-4">
                    <?php if ($greeting !== ''): ?>
                        <span class="text-primary text-uppercase fw-semibold d-inline-block mb-3">
                            <?= htmlspecialchars($greeting) ?>
                        </span>
                    <?php endif; ?>
                    <h1 class="about-title display-5 fw-bold mb-4"><?= htmlspecialchars($headline) ?></h1>
                    <?php if ($bio !== ''): ?>
                        <div class="about-bio fs-5 text-body-secondary mb-4">
                            <?= nl2br(htmlspecialchars($bio)) ?>
                        </div>
                    <?php endif; ?>
                    <?php if ($philosophy !== ''): ?>
                        <div class="about-philosophy p-4 bg-light rounded-4 border">
                            <div class="d-flex align-items-start">
                                <i class="bi bi-lightbulb text-primary fs-3 me-3"></i>
                                <p class="mb-0 fst-italic"><?= nl2br(htmlspecialchars($philosophy)) ?></p>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section><?php endif; ?>

<?php if (WebsiteContent::visible('about.section.experience-section')): ?><section data-section-key="about.section.experience-section" class="experience-section py-5 bg-light">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto text-center" data-aos="fade-up">
                <h2 class="section-title mb-3"><?= htmlspecialchars($timelineTitle) ?></h2>
                <?php if (!empty($timelineSubtitle)): ?>
                    <p class="section-subtitle text-muted mb-5">
                        <?= htmlspecialchars($timelineSubtitle) ?>
                    </p>
                <?php endif; ?>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-10 mx-auto">
                <div class="timeline">
                    <?php if ($hasTimelineContent): ?>
                        <?php $timelineIndex = 0; ?>
                        <?php foreach ($timelineGroups as $groupKey => $groupData): ?>
                            <?php if (empty($groupData['items'])) { continue; } ?>
                            <div class="timeline-group">
                                <h5 class="timeline-group-title mb-4 text-primary">
                                    <?= htmlspecialchars($groupData['label']) ?>
                                </h5>
                                <?php foreach ($groupData['items'] as $item): ?>
                                    <?php
                                    $delay = (int)$timelineIndex * 100;
                                    $timelineIndex++;
                                    $organization = $item['organization'] ?? ($item['company'] ?? '');
                                    $dateRange = $item['date_range'] ?? ($item['date'] ?? '');
                                    $tags = $item['tags'] ?? [];
                                    if (is_string($tags)) {
                                        $decodedTags = json_decode($tags, true);
                                        $tags = json_last_error() === JSON_ERROR_NONE ? $decodedTags : [];
                                    }
                                    if (!is_array($tags)) {
                                        $tags = [];
                                    }
                                    ?>
                                    <div class="timeline-item"<?php if(!empty($wfeEditing)): ?> data-collection-type="experience" data-collection-item="<?= (int)$item['id'] ?>"<?php endif; ?> data-aos="fade-up" data-aos-delay="<?= $delay ?>">
                                        <div class="timeline-marker<?= $groupKey === 'education' ? ' timeline-marker-education' : '' ?>"></div>
                                        <div class="timeline-content">
                                            <div class="timeline-header">
                                                <h4 class="timeline-title"><?= htmlspecialchars($item['title'] ?? '') ?></h4>
                                                <?php if (!empty($organization)): ?>
                                                    <span class="timeline-company"><?= htmlspecialchars($organization) ?></span>
                                                <?php endif; ?>
                                                <?php if (!empty($dateRange)): ?>
                                                    <span class="timeline-date"><?= htmlspecialchars($dateRange) ?></span>
                                                <?php endif; ?>
                                            </div>
                                            <?php if (!empty($item['description'])): ?>
                                                <p class="timeline-description">
                                                    <?= htmlspecialchars($item['description']) ?>
                                                </p>
                                            <?php endif; ?>
                                            <?php if (!empty($tags)): ?>
                                                <div class="timeline-skills">
                                                    <?php foreach ($tags as $tag): ?>
                                                        <span class="skill-tag"><?= htmlspecialchars($tag) ?></span>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center py-4 text-muted">
                            <i class="bi bi-info-circle fs-3 d-block mb-2"></i>
                            <p class="mb-0"><?= site_text('about.text.4521a4f05dbe', 'No timeline entries yet. Add milestones in the admin') ?><a href="<?= url('admin/timeline') ?>"><?= site_text('about.text.018514a3d58a', 'Timeline') ?></a><?= site_text('about.text.76faaac560c0', 'to display them here.') ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section><?php endif; ?>

<?php if (!empty($skillsByCategory)): ?>
<?php if (WebsiteContent::visible('about.section.skills-detail-section')): ?><section data-section-key="about.section.skills-detail-section" class="skills-detail-section py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto text-center" data-aos="fade-up">
                <h2 class="section-title mb-3"><?= site_text('about.text.caba7908b133', 'Skills &amp; Expertise') ?></h2>
                <p class="section-subtitle text-muted mb-5"><?= site_text('about.text.882712d4231a', 'A breakdown of the capabilities, tools, and disciplines I practice every day.') ?></p>
            </div>
        </div>
        <div class="row">
            <?php $delay = 100; ?>
            <?php foreach ($skillsByCategory as $categoryName => $group): ?>
                <?php
                $categoryMeta = $group['meta'] ?? [];
                $skillsGroup = $group['items'] ?? [];
                ?>
                <div class="col-lg-6 mb-4" data-aos="fade-up" data-aos-delay="<?= $delay ?>">
                    <div class="skill-group">
                        <h4 class="skill-group-title mb-4"><?= htmlspecialchars($categoryName ?? 'Skills') ?></h4>
                        <div class="skill-bars">
                            <?php foreach ($skillsGroup as $skill): ?>
                                <div class="skill-bar mb-3"<?php if(!empty($wfeEditing)): ?> data-collection-type="skills" data-collection-item="<?= (int)$skill['id'] ?>"<?php endif; ?>>
                                    <div class="skill-info">
                                        <span class="skill-name"><?= htmlspecialchars($skill['skill_name'] ?? $skill['name'] ?? '') ?></span>
                                        <?php if (!empty($skill['proficiency_level'])): ?>
                                            <span class="skill-percentage"><?= (int)$skill['proficiency_level'] ?>%</span>
                                        <?php endif; ?>
                                    </div>
                                    <?php if (!empty($skill['proficiency_level'])): ?>
                                        <div class="progress">
                                            <div class="progress-bar" style="width: <?= (int)$skill['proficiency_level'] ?>%"></div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <?php $delay += 100; ?>
            <?php endforeach; ?>
        </div>
    </div>
</section><?php endif; ?>
<?php endif; ?>


<?php if (!empty($testimonials)): ?>
<?php if (WebsiteContent::visible('about.section.testimonials-section')): ?><section data-section-key="about.section.testimonials-section" class="testimonials-section py-5 bg-light">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto text-center" data-aos="fade-up">
                <h2 class="section-title mb-3"><?= site_text('about.text.9e1785b699ae', 'What Clients Say') ?></h2>
                <p class="section-subtitle text-muted mb-5"><?= site_text('about.text.36b50b6b9154', 'Insights from clients and teams I\'ve partnered with.') ?></p>
            </div>
        </div>
        <div class="row">
            <?php foreach ($testimonials as $index => $testimonial): ?>
                <?php
                $avatar = !empty($testimonial['image_url']) ? $testimonial['image_url'] : asset('images/placeholder.svg');
                $testimonialText = $testimonial['testimonial_text'] ?? '';
                $rating = (int)($testimonial['rating'] ?? 5);
                ?>
                <div class="col-lg-4 mb-4" data-aos="fade-up" data-aos-delay="<?= (int)$index * 100 ?>">
                    <div class="testimonial-card"<?php if(!empty($wfeEditing)): ?> data-collection-type="testimonials" data-collection-item="<?= (int)$testimonial['id'] ?>"<?php endif; ?>>
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
</section><?php endif; ?>
<?php endif; ?>

<!-- CTA Section -->
<?php if (WebsiteContent::visible('about.section.cta-section')): ?><section data-section-key="about.section.cta-section" class="cta-section py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto text-center" data-aos="fade-up">
                <h2 class="cta-title mb-3"><?= site_text('about.text.28e7ac9a04dd', 'Let\'s Work Together') ?></h2>
                <p class="cta-subtitle text-muted mb-4"><?= site_text('about.text.c951eb5bdcb3', 'I\'m always excited to take on new challenges and help bring ideas to life.') ?></p>
                <div class="cta-actions">
                    <a href="<?= url('contact') ?>" class="btn btn-primary btn-lg me-3">
                        <i class="bi bi-envelope me-2"></i><?= site_text('about.text.26e00b08d068', 'Get In Touch') ?></a>
                    <a href="<?= url('portfolio') ?>" class="btn btn-outline-primary btn-lg">
                        <i class="bi bi-collection me-2"></i><?= site_text('about.text.0259cb7a9856', 'View Portfolio') ?></a>
                </div>
            </div>
        </div>
    </div>
</section><?php endif; ?>