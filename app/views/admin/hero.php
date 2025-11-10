<?php
$heroForm = $hero_form ?? [];
$heroPreview = $hero_preview ?? [];
$heroStats = $hero_stats ?? [];
$previewStats = $hero_preview_stats ?? [];
$heroStatsPayload = array_map(function ($stat) {
    $statId = (int)($stat['id'] ?? 0);

    return [
        'id' => $statId,
        'label' => $stat['label'] ?? '',
        'value' => $stat['value'] ?? '',
        'is_active' => (int)($stat['is_active'] ?? 0),
        'sort_order' => (int)($stat['sort_order'] ?? 0),
        'update_url' => url('admin/hero/stats/' . $statId . '/edit'),
        'delete_url' => url('admin/hero/stats/' . $statId . '/delete')
    ];
}, $heroStats);
$heroStatsLimit = 3;
$heroStatsJson = htmlspecialchars(json_encode($heroStatsPayload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), ENT_QUOTES, 'UTF-8');

$heroImageUrl = $heroPreview['hero_background_image_url'] ?? asset('images/hero-portrait.jpg');
$primaryCta = $heroPreview['hero_primary_cta'] ?? [
    'text' => 'View My Work',
    'url' => navbar_build_nav_url('/portfolio')
];
$secondaryCta = $heroPreview['hero_secondary_cta'] ?? [
    'text' => "Let's Talk",
    'url' => navbar_build_nav_url('/contact')
];

$scrollIndicatorValue = $heroForm['scroll_indicator_text'] ?? 'Scroll to explore';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">Hero Section</h1>
                    <p class="text-muted mb-0">Manage the hero headline, CTAs, and supporting stats. Changes preview instantly.</p>
                </div>
            </div>
        </div>
    </div>

    <form method="POST" action="<?= url('admin/hero') ?>" enctype="multipart/form-data" class="admin-form">
        <div class="row">
            <div class="col-xl-8">
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title mb-0">
                                <i class="bi bi-star me-2"></i>
                                Hero Content
                            </h5>
                            <small class="text-muted">Update the hero copy, CTAs, and imagery.</small>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row g-3 mb-4">
                            <div class="col-md-3">
                                <label for="hero_intro_prefix" class="form-label">Intro Prefix</label>
                                <input type="text"
                                       class="form-control form-control-lg"
                                       id="hero_intro_prefix"
                                       name="hero_intro_prefix"
                                       value="<?= htmlspecialchars($heroForm['hero_intro_prefix'] ?? "Hi, I'm") ?>"
                                       placeholder="Hi, I'm"
                                       data-preview-target=".preview-hero-intro-prefix">
                            </div>
                            <div class="col-md-3">
                                <label for="hero_intro_name_first" class="form-label">First Name</label>
                                <input type="text"
                                       class="form-control form-control-lg"
                                       id="hero_intro_name_first"
                                       name="hero_intro_name_first"
                                       value="<?= htmlspecialchars($heroForm['hero_intro_name_first'] ?? '') ?>"
                                       placeholder="Alexandra"
                                       data-preview-target=".preview-hero-intro-first">
                            </div>
                            <div class="col-md-3">
                                <label for="hero_intro_name_rest" class="form-label">Last Name</label>
                                <input type="text"
                                       class="form-control form-control-lg"
                                       id="hero_intro_name_rest"
                                       name="hero_intro_name_rest"
                                       value="<?= htmlspecialchars($heroForm['hero_intro_name_rest'] ?? '') ?>"
                                       placeholder="Hart"
                                       data-preview-target=".preview-hero-intro-rest">
                            </div>
                            <div class="col-md-3">
                                <label for="hero_intro_suffix" class="form-label">Role Suffix</label>
                                <input type="text"
                                       class="form-control form-control-lg"
                                       id="hero_intro_suffix"
                                       name="hero_intro_suffix"
                                       value="<?= htmlspecialchars($heroForm['hero_intro_suffix'] ?? '') ?>"
                                       placeholder="Product Designer"
                                       data-preview-target=".preview-hero-intro-suffix">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="hero_title" class="form-label">Headline</label>
                            <input type="text"
                                   class="form-control form-control-lg"
                                   id="hero_title"
                                   name="hero_title"
                                   value="<?= htmlspecialchars($heroForm['hero_title'] ?? 'Creative UI/UX Designer') ?>"
                                   placeholder="Creative UI/UX Designer"
                                   data-preview-target=".preview-hero-title">
                        </div>

                        <div class="mb-3">
                            <label for="hero_subtitle" class="form-label">Subtitle</label>
                            <input type="text"
                                   class="form-control"
                                   id="hero_subtitle"
                                   name="hero_subtitle"
                                   value="<?= htmlspecialchars($heroForm['hero_subtitle'] ?? '') ?>"
                                   placeholder="Transforming ideas into beautiful digital experiences"
                                   data-preview-target=".preview-hero-subtitle">
                        </div>

                        <div class="mb-4">
                            <label for="hero_description" class="form-label">Supporting Description</label>
                            <textarea class="form-control"
                                      id="hero_description"
                                      name="hero_description"
                                      rows="4"
                                      placeholder="Brief description of your services and expertise"
                                      data-preview-target=".preview-hero-description"
                                      data-preview-property="html"><?= htmlspecialchars($heroForm['hero_description'] ?? '') ?></textarea>
                            <div class="form-text">Supports multi-line content. Preview formats paragraphs automatically.</div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label for="hero_primary_cta_text" class="form-label">Primary CTA Text</label>
                                <input type="text"
                                       class="form-control"
                                       id="hero_primary_cta_text"
                                       name="hero_primary_cta_text"
                                       value="<?= htmlspecialchars($heroForm['hero_primary_cta_text'] ?? 'View My Work') ?>"
                                       placeholder="View My Work"
                                       data-preview-target=".preview-hero-primary-cta-text">
                            </div>
                            <div class="col-md-6">
                                <label for="hero_primary_cta_url" class="form-label">Primary CTA URL</label>
                                <input type="text"
                                       class="form-control"
                                       id="hero_primary_cta_url"
                                       name="hero_primary_cta_url"
                                       value="<?= htmlspecialchars($heroForm['hero_primary_cta_url'] ?? '/portfolio') ?>"
                                       placeholder="/portfolio">
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label for="hero_secondary_cta_text" class="form-label">Secondary CTA Text</label>
                                <input type="text"
                                       class="form-control"
                                       id="hero_secondary_cta_text"
                                       name="hero_secondary_cta_text"
                                       value="<?= htmlspecialchars($heroForm['hero_secondary_cta_text'] ?? "Let's Talk") ?>"
                                       placeholder="Let's Talk"
                                       data-preview-target=".preview-hero-secondary-cta-text">
                            </div>
                            <div class="col-md-6">
                                <label for="hero_secondary_cta_url" class="form-label">Secondary CTA URL</label>
                                <input type="text"
                                       class="form-control"
                                       id="hero_secondary_cta_url"
                                       name="hero_secondary_cta_url"
                                       value="<?= htmlspecialchars($heroForm['hero_secondary_cta_url'] ?? '/contact') ?>"
                                       placeholder="/contact">
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-8">
                                <label for="hero_background_image_path" class="form-label">Hero Image (URL)</label>
                                <input type="url"
                                       class="form-control"
                                       id="hero_background_image_path"
                                       name="hero_background_image_path"
                                       value="<?= htmlspecialchars($heroForm['hero_background_image_path'] ?? '') ?>"
                                       placeholder="https://example.com/hero.jpg"
                                       data-preview-image=".preview-hero-image">
                                <div class="form-text">Paste an external image URL or upload a file below.</div>
                            </div>
                            <div class="col-md-4">
                                <label for="hero_background_image_file" class="form-label">Upload Image</label>
                                <input type="file"
                                       class="form-control"
                                       id="hero_background_image_file"
                                       name="hero_background_image_file"
                                       accept="image/*"
                                       data-preview-file=".preview-hero-image">
                            </div>
                        </div>

                        <div class="row g-3 mt-0">
                            <div class="col-md-6">
                                <label for="hero_background_image_alt" class="form-label">Image Alt Text</label>
                                <input type="text"
                                       class="form-control"
                                       id="hero_background_image_alt"
                                       name="hero_background_image_alt"
                                       value="<?= htmlspecialchars($heroForm['hero_background_image_alt'] ?? 'Portrait') ?>"
                                       placeholder="Alexandra Hart portrait">
                            </div>
                            <div class="col-md-6">
                                <label for="scroll_indicator_text" class="form-label">Scroll Indicator Text</label>
                                <input type="text"
                                       class="form-control"
                                       id="scroll_indicator_text"
                                       name="scroll_indicator_text"
                                       value="<?= htmlspecialchars($scrollIndicatorValue) ?>"
                                       placeholder="Scroll to explore"
                                       data-preview-target=".preview-hero-scroll-text"
                                       data-preview-toggle-selector=".hero-preview-scroll"
                                       data-preview-toggle-class="d-none">
                                <div class="form-text">Leave blank to hide the scroll indicator in the hero.</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-4">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-eye me-2"></i>
                            Live Preview
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="hero-preview-card position-relative overflow-hidden rounded text-white">
                            <div class="hero-preview-overlay"></div>
                            <img src="<?= htmlspecialchars($heroImageUrl) ?>"
                                 alt="<?= htmlspecialchars($heroForm['hero_background_image_alt'] ?? 'Hero image preview') ?>"
                                 class="preview-hero-image hero-preview-background">
                            <div class="hero-preview-content position-relative">
                                <p class="text-uppercase small fw-semibold mb-2">
                                    <span class="preview-hero-intro-prefix d-inline-block me-1"><?= htmlspecialchars($heroForm['hero_intro_prefix'] ?? "Hi, I'm") ?></span>
                                    <span class="preview-hero-intro-first d-inline-block fw-bold"><?= htmlspecialchars($heroForm['hero_intro_name_first'] ?? '') ?></span>
                                    <?php if (!empty($heroForm['hero_intro_name_rest'])): ?>
                                        <span class="preview-hero-intro-rest d-inline-block ms-1 fw-bold"><?= htmlspecialchars($heroForm['hero_intro_name_rest']) ?></span>
                                    <?php else: ?>
                                        <span class="preview-hero-intro-rest d-inline-block ms-1 fw-bold"></span>
                                    <?php endif; ?>
                                    <span class="preview-hero-intro-suffix d-inline-block ms-1 text-warning"><?= htmlspecialchars($heroForm['hero_intro_suffix'] ?? '') ?></span>
                                </p>
                                <h2 class="preview-hero-title mb-3"><?= htmlspecialchars($heroForm['hero_title'] ?? 'Creative UI/UX Designer') ?></h2>
                                <h5 class="preview-hero-subtitle text-light mb-3"><?= htmlspecialchars($heroForm['hero_subtitle'] ?? '') ?></h5>
                                <p class="preview-hero-description text-light-50 mb-4">
                                    <?= nl2br(htmlspecialchars($heroForm['hero_description'] ?? 'I help product teams ship delightful, accessible experiences that drive real outcomes.')) ?>
                                </p>
                                <div class="d-flex flex-wrap gap-2">
                                    <a href="<?= htmlspecialchars($primaryCta['url']) ?>" class="btn btn-light btn-sm preview-hero-primary-cta">
                                        <span class="preview-hero-primary-cta-text"><?= htmlspecialchars($primaryCta['text']) ?></span>
                                    </a>
                                    <a href="<?= htmlspecialchars($secondaryCta['url']) ?>" class="btn btn-outline-light btn-sm preview-hero-secondary-cta">
                                        <span class="preview-hero-secondary-cta-text"><?= htmlspecialchars($secondaryCta['text']) ?></span>
                                    </a>
                                </div>
                                <div class="hero-preview-stats mt-4">
                                    <div class="row g-3" data-hero-stats-preview>
                                        <?php $activePreviewStats = array_filter($previewStats, function ($stat) {
                                            return (int)($stat['is_active'] ?? 0) === 1;
                                        }); ?>
                                        <?php if (!empty($activePreviewStats)): ?>
                                            <?php foreach (array_slice($activePreviewStats, 0, 3) as $stat): ?>
                                                <div class="col-4">
                                                    <div class="fw-semibold h4 mb-1"><?= htmlspecialchars($stat['value'] ?? '') ?></div>
                                                    <div class="text-uppercase small"><?= htmlspecialchars($stat['label'] ?? '') ?></div>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <div class="col-12 text-light-50 small">Add hero stats to highlight experience.</div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="hero-preview-scroll mt-4 <?= trim($scrollIndicatorValue) === '' ? 'd-none' : '' ?>">
                                    <div class="d-inline-flex align-items-center gap-2 px-3 py-2 rounded-pill bg-dark bg-opacity-50 text-white small">
                                        <i class="bi bi-mouse"></i>
                                        <span class="preview-hero-scroll-text"><?= htmlspecialchars($scrollIndicatorValue) ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mt-3">
                            <small class="text-muted">
                                <i class="bi bi-info-circle me-1"></i>
                                Preview approximates homepage styling; final appearance follows public theme.
                            </small>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-outline-secondary" data-form-cancel>Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-2"></i>
                        Save Hero Content
                    </button>
                </div>
            </div>
        </div>
    </form>

    <div class="row mt-4">
        <div class="col-xl-8">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title mb-0">
                            <i class="bi bi-graph-up-arrow me-2"></i>
                            Hero Stats
                        </h5>
                        <small class="text-muted">Showcase quick wins or credentials alongside the hero copy.</small>
                    </div>
                    <span class="badge bg-primary-subtle text-primary" data-hero-stats-count><?= count($heroStats) ?> total</span>
                </div>
                <div class="card-body"
                     data-hero-stats-manager
                     data-hero-stats-limit="<?= $heroStatsLimit ?>"
                     data-hero-stats-create-url="<?= url('admin/hero/stats') ?>"
                     data-hero-stats-reorder-url="<?= url('admin/hero/stats/reorder') ?>"
                     data-hero-stats-initial="<?= $heroStatsJson ?>">
                    <h6 class="text-uppercase text-muted fs-sm mb-3">Add Stat</h6>
                    <form class="row g-2 align-items-end mb-3" data-hero-stat-add-form data-auto-loading="false">
                        <div class="col-md-5">
                            <label for="new_stat_label" class="form-label">Label</label>
                            <input type="text" class="form-control" id="new_stat_label" name="label" placeholder="Years crafting digital products" data-hero-stat-add-label required>
                        </div>
                        <div class="col-md-4">
                            <label for="new_stat_value" class="form-label">Value</label>
                            <input type="text" class="form-control" id="new_stat_value" name="value" placeholder="8+ Years" data-hero-stat-add-value required>
                        </div>
                        <div class="col-md-2">
                            <div class="form-check mt-4 pt-2">
                                <input class="form-check-input" type="checkbox" value="1" id="new_stat_active" name="is_active" data-hero-stat-add-visible checked>
                                <label class="form-check-label" for="new_stat_active">Visible</label>
                            </div>
                        </div>
                        <div class="col-md-1 d-grid">
                            <button type="submit" class="btn btn-primary" data-hero-stat-add-button>
                                <i class="bi bi-plus-lg"></i>
                                <span class="visually-hidden">Add stat</span>
                            </button>
                        </div>
                    </form>
                    <div class="small text-muted mb-4 d-none" data-hero-stats-feedback></div>

                    <ul class="list-group hero-stats-list"
                        data-hero-stats-list
                        data-sortable-list
                        data-hero-stats-limit="<?= $heroStatsLimit ?>">
                    </ul>

                    <div class="text-center py-4 text-muted border rounded d-none" data-hero-stats-empty>
                        <i class="bi bi-graph-up fs-4 mb-2"></i>
                        <p class="mb-1">No hero stats yet.</p>
                        <small>Add your first stat above to highlight achievements.</small>
                    </div>

                    <template id="heroStatItemTemplate">
                        <li class="list-group-item hero-stat-item d-flex align-items-start gap-3 flex-wrap"
                            data-hero-stat-item
                            data-sortable-item>
                            <span class="sortable-handle text-muted" title="Drag to reorder" data-sortable-handle>
                                <i class="bi bi-grip-vertical"></i>
                            </span>
                            <div class="flex-grow-1 w-100">
                                <div class="row g-2 align-items-end">
                                    <div class="col-lg-4">
                                        <label class="form-label">Label</label>
                                        <input type="text" class="form-control form-control-sm" placeholder="Metric label" data-hero-stat-label>
                                    </div>
                                    <div class="col-lg-3">
                                        <label class="form-label">Value</label>
                                        <input type="text" class="form-control form-control-sm" placeholder="e.g. 8+ Years" data-hero-stat-value>
                                    </div>
                                    <div class="col-lg-2">
                                        <div class="form-check pt-lg-4">
                                            <input class="form-check-input" type="checkbox" value="1" data-hero-stat-visible>
                                            <label class="form-check-label">Visible</label>
                                        </div>
                                    </div>
                                    <div class="col-lg-3 d-flex align-items-center justify-content-end gap-2">
                                        <span class="hero-stat-feedback small text-muted" data-hero-stat-feedback></span>
                                        <button type="button" class="btn btn-outline-primary btn-sm" data-hero-stat-save>
                                            <i class="bi bi-save"></i>
                                            <span class="ms-1">Save</span>
                                        </button>
                                        <button type="button" class="btn btn-outline-danger btn-sm" data-hero-stat-delete title="Delete stat">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </li>
                    </template>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-lightbulb me-2"></i>
                        Content Guidelines
                    </h5>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled small mb-0">
                        <li class="mb-2"><strong>Hero Title:</strong> Aim for 50–70 characters focused on the value proposition.</li>
                        <li class="mb-2"><strong>Subtitle:</strong> Reinforce your specialization or target audience.</li>
                        <li class="mb-2"><strong>Description:</strong> Use short paragraphs or bullet-style lines to keep it scannable.</li>
                        <li class="mb-2"><strong>Hero Stats:</strong> Limit to three high-impact metrics. Toggle visibility when rotating themes.</li>
                        <li><strong>Imagery:</strong> Use a 3:4 portrait or transparent-background illustration for best results.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
