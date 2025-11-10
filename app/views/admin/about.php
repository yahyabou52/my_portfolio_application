<?php
$aboutForm = $about_form ?? [];
$aboutPreview = $about_preview ?? [];
$timelineItems = $timeline_items ?? [];

$greetingValue = $aboutForm['about_greeting'] ?? '';
$headlineValue = $aboutForm['about_headline'] ?? 'About Me';
$bioValue = $aboutForm['about_bio'] ?? '';
$philosophyValue = $aboutForm['about_philosophy'] ?? '';
$imagePathValue = $aboutForm['about_image_path'] ?? '';
$imageAltValue = $aboutForm['about_image_alt'] ?? 'Portrait';
$timelineTitleValue = $aboutForm['timeline_title'] ?? 'Experience & Education';
$timelineSubtitleValue = $aboutForm['timeline_subtitle'] ?? '';

$previewImageUrl = $aboutPreview['image_url'] ?? asset('images/about-photo.jpg');
$previewImageAlt = $aboutPreview['image_alt'] ?? 'About portrait';
$previewGreeting = $aboutPreview['greeting'] ?? '';
$previewHeadline = $aboutPreview['headline'] ?? 'About Me';
$previewBio = $aboutPreview['bio'] ?? '';
$previewPhilosophy = $aboutPreview['philosophy'] ?? '';
$previewTimelineTitle = $aboutPreview['timeline_title'] ?? 'Experience & Education';
$previewTimelineSubtitle = $aboutPreview['timeline_subtitle'] ?? '';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">About Section</h1>
                    <p class="text-muted mb-0">Shape the story, imagery, and supporting narrative visitors see on your About page.</p>
                </div>
            </div>
        </div>
    </div>

    <form method="POST" action="<?= url('admin/about') ?>" enctype="multipart/form-data" class="admin-form">
        <div class="row">
            <div class="col-xl-7">
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title mb-0">
                                <i class="bi bi-person-lines-fill me-2"></i>
                                About Content
                            </h5>
                            <small class="text-muted">Craft the hero message, biography, and supporting narrative.</small>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-lg-6">
                                <label for="about_greeting" class="form-label">Greeting Line</label>
                                <input type="text"
                                       class="form-control"
                                       id="about_greeting"
                                       name="about_greeting"
                                       value="<?= htmlspecialchars($greetingValue) ?>"
                                       maxlength="120"
                                       placeholder="Hello, I'm Yahya"
                                       data-preview-target=".preview-about-greeting"
                                       data-preview-toggle-selector=".preview-about-greeting-wrapper"
                                       data-preview-toggle-class="d-none">
                                <div class="form-text">A short intro that sets the tone. Example: "Hello, I'm Yahya".</div>
                            </div>
                            <div class="col-lg-6">
                                <label for="about_headline" class="form-label">Headline</label>
                                <input type="text"
                                       class="form-control form-control-lg"
                                       id="about_headline"
                                       name="about_headline"
                                       value="<?= htmlspecialchars($headlineValue) ?>"
                                       maxlength="160"
                                       placeholder="Designing with empathy, strategy, and measurable results"
                                       data-preview-target=".preview-about-headline">
                            </div>
                        </div>

                        <div class="mt-4">
                            <label for="about_bio" class="form-label">Short Biography</label>
                            <textarea class="form-control"
                                      id="about_bio"
                                      name="about_bio"
                                      rows="5"
                                      maxlength="1200"
                                      placeholder="Share a concise overview of your background, focus, and who you partner with."
                                      data-preview-target=".preview-about-bio"
                                      data-preview-property="html"
                                      data-preview-toggle-selector=".preview-about-bio"
                                      data-preview-toggle-class="d-none"><?= htmlspecialchars($bioValue) ?></textarea>
                            <div class="form-text">Keep to one or two paragraphs. Line breaks are preserved in the preview.</div>
                        </div>

                        <div class="mt-4">
                            <label for="about_philosophy" class="form-label">Design Philosophy</label>
                            <textarea class="form-control"
                                      id="about_philosophy"
                                      name="about_philosophy"
                                      rows="4"
                                      maxlength="900"
                                      placeholder="Explain how you approach design, collaboration, and measurable outcomes."
                                      data-preview-target=".preview-about-philosophy-text"
                                      data-preview-property="html"
                                      data-preview-toggle-selector=".preview-about-philosophy"
                                      data-preview-toggle-class="d-none"><?= htmlspecialchars($philosophyValue) ?></textarea>
                            <div class="form-text">Optional. Appears as a highlighted insight in the preview.</div>
                        </div>

                        <hr class="my-4">

                        <div class="row g-3">
                            <div class="col-md-7">
                                <label for="about_image_path" class="form-label">Portrait Image (URL)</label>
                                <input type="url"
                                       class="form-control"
                                       id="about_image_path"
                                       name="about_image_path"
                                       value="<?= htmlspecialchars($imagePathValue) ?>"
                                       placeholder="https://example.com/about-photo.jpg"
                                       data-preview-image=".preview-about-image">
                                <div class="form-text">Paste an external image URL or upload a file below.</div>
                            </div>
                            <div class="col-md-5">
                                <label for="about_image_file" class="form-label">Upload Image</label>
                                <input type="file"
                                       class="form-control"
                                       id="about_image_file"
                                       name="about_image_file"
                                       accept="image/*"
                                       data-preview-file=".preview-about-image">
                            </div>
                        </div>

                        <div class="row g-3 mt-0">
                            <div class="col-md-6">
                                <label for="about_image_alt" class="form-label">Image Alt Text</label>
                                <input type="text"
                                       class="form-control"
                                       id="about_image_alt"
                                       name="about_image_alt"
                                       value="<?= htmlspecialchars($imageAltValue) ?>"
                                       maxlength="160"
                                       placeholder="Portrait of Yahya Bouhafs">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title mb-0">
                                <i class="bi bi-clock-history me-2"></i>
                                Timeline Heading
                            </h5>
                            <small class="text-muted">Controls the heading that introduces your experience timeline.</small>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="timeline_title" class="form-label">Timeline Title</label>
                                <input type="text"
                                       class="form-control"
                                       id="timeline_title"
                                       name="timeline_title"
                                       value="<?= htmlspecialchars($timelineTitleValue) ?>"
                                       maxlength="160"
                                       placeholder="Experience & Education"
                                       data-preview-target=".preview-timeline-title">
                            </div>
                            <div class="col-md-6">
                                <label for="timeline_subtitle" class="form-label">Timeline Subtitle</label>
                                <input type="text"
                                       class="form-control"
                                       id="timeline_subtitle"
                                       name="timeline_subtitle"
                                       value="<?= htmlspecialchars($timelineSubtitleValue) ?>"
                                       maxlength="200"
                                       placeholder="A journey of experimentation, leadership, and continuous learning."
                                       data-preview-target=".preview-timeline-subtitle"
                                       data-preview-toggle-selector=".preview-timeline-subtitle"
                                       data-preview-toggle-class="d-none">
                            </div>
                        </div>
                        <div class="form-text mt-3">Manage individual timeline entries in the Timeline module (coming next).</div>
                    </div>
                </div>
            </div>

            <div class="col-xl-5">
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title mb-0">
                                <i class="bi bi-eye me-2"></i>
                                Live Preview
                            </h5>
                            <small class="text-muted">Updates instantly as you edit the form.</small>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="about-preview-card border rounded-4 overflow-hidden shadow-sm">
                            <div class="preview-about-image-wrapper position-relative">
                                <img src="<?= htmlspecialchars($previewImageUrl) ?>"
                                     alt="<?= htmlspecialchars($previewImageAlt) ?>"
                                     class="preview-about-image w-100">
                            </div>
                            <div class="p-4">
                                <div class="preview-about-greeting-wrapper text-primary text-uppercase fw-semibold small mb-3 <?= trim($previewGreeting) === '' ? 'd-none' : '' ?>">
                                    <span class="preview-about-greeting"><?= htmlspecialchars($previewGreeting) ?></span>
                                </div>
                                <h3 class="preview-about-headline fw-bold mb-3"><?= htmlspecialchars($previewHeadline) ?></h3>
                                <div class="preview-about-bio text-body-secondary mb-4 <?= trim($previewBio) === '' ? 'd-none' : '' ?>">
                                    <?= nl2br(htmlspecialchars($previewBio)) ?>
                                </div>
                                <div class="preview-about-philosophy bg-light border rounded-4 p-4 d-flex align-items-start gap-3 <?= trim($previewPhilosophy) === '' ? 'd-none' : '' ?>">
                                    <i class="bi bi-lightbulb text-primary fs-3"></i>
                                    <div class="preview-about-philosophy-text mb-0">
                                        <?= nl2br(htmlspecialchars($previewPhilosophy)) ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="timeline-preview mt-4 border rounded-4 p-3 bg-light-subtle">
                            <div class="d-flex align-items-center mb-3">
                                <div>
                                    <h6 class="mb-1 preview-timeline-title"><?= htmlspecialchars($previewTimelineTitle) ?></h6>
                                    <small class="text-muted preview-timeline-subtitle <?= trim($previewTimelineSubtitle) === '' ? 'd-none' : '' ?>">
                                        <?= htmlspecialchars($previewTimelineSubtitle) ?>
                                    </small>
                                </div>
                            </div>
                            <ul class="timeline-preview-list list-unstyled mb-0">
                                <?php if (!empty($timelineItems)): ?>
                                    <?php foreach (array_slice($timelineItems, 0, 3) as $item): ?>
                                        <li class="timeline-preview-item py-2 border-bottom">
                                            <div class="fw-semibold"><?= htmlspecialchars($item['title'] ?? '') ?></div>
                                            <div class="text-muted small">
                                                <?= htmlspecialchars($item['organization'] ?? ($item['company'] ?? '')) ?>
                                                <?php if (!empty($item['date_range'])): ?>
                                                    <span class="text-secondary">&middot; <?= htmlspecialchars($item['date_range']) ?></span>
                                                <?php endif; ?>
                                            </div>
                                        </li>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <li class="timeline-preview-item text-muted small">Timeline entries will appear here once added.</li>
                                <?php endif; ?>
                            </ul>
                        </div>

                        <div class="mt-3">
                            <small class="text-muted">
                                <i class="bi bi-info-circle me-1"></i>
                                Preview approximates the public About layout. Final styling follows the live theme.
                            </small>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-outline-secondary" data-form-cancel>Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-2"></i>
                        Save About Content
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
