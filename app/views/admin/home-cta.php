<?php
$ctaForm = $cta_form ?? [];
$ctaPreview = $cta_preview ?? $ctaForm;
$ctaRoutes = $cta_routes ?? [];
$initialJson = $cta_initial_json ?? json_encode($ctaForm, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
$updateUrl = $cta_update_url ?? url('admin/home/page-cta');

$initialPayload = htmlspecialchars($initialJson, ENT_QUOTES, 'UTF-8');
$updateUrlEsc = htmlspecialchars($updateUrl, ENT_QUOTES, 'UTF-8');

$primaryRouteValue = $ctaForm['primary_cta_url'] ?? '';
$secondaryRouteValue = $ctaForm['secondary_cta_url'] ?? '';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">Homepage CTA</h1>
                    <p class="text-muted mb-0">Control the final call-to-action block visitors see on the homepage.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4"
         data-home-cta
         data-home-cta-initial="<?= $initialPayload ?>"
         data-home-cta-update-url="<?= $updateUrlEsc ?>">
        <div class="col-xl-7">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title mb-0">
                            <i class="bi bi-megaphone me-2"></i>
                            CTA Content
                        </h5>
                        <small class="text-muted">Update the copy and button destinations. Live preview updates on the right.</small>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-secondary btn-sm" data-home-cta-cancel disabled>Cancel</button>
                        <button type="button" class="btn btn-primary btn-sm" data-home-cta-save>
                            <i class="bi bi-check-lg me-2"></i>
                            Save Changes
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <form class="admin-form" data-home-cta-form>
                        <div class="alert alert-danger d-none" data-home-cta-errors></div>

                        <div class="mb-3">
                            <label for="home_cta_title" class="form-label">Headline<span class="text-danger ms-1">*</span></label>
                            <input type="text"
                                   class="form-control form-control-lg"
                                   id="home_cta_title"
                                   name="title"
                                   value="<?= htmlspecialchars($ctaForm['title'] ?? '') ?>"
                                   maxlength="150"
                                   required
                                   data-home-cta-field="title">
                            <div class="form-text">Keep it action oriented and under 150 characters.</div>
                        </div>

                        <div class="mb-4">
                            <label for="home_cta_subtitle" class="form-label">Supporting Text</label>
                            <textarea class="form-control"
                                      id="home_cta_subtitle"
                                      name="subtitle"
                                      rows="3"
                                      maxlength="240"
                                      placeholder="Why should visitors take action?"
                                      data-home-cta-field="subtitle"><?= htmlspecialchars($ctaForm['subtitle'] ?? '') ?></textarea>
                            <div class="form-text">Leave blank to hide the supporting copy.</div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label for="home_cta_primary_text" class="form-label">Primary Button Text<span class="text-danger ms-1">*</span></label>
                                <input type="text"
                                       class="form-control"
                                       id="home_cta_primary_text"
                                       name="primary_cta_text"
                                       value="<?= htmlspecialchars($ctaForm['primary_cta_text'] ?? '') ?>"
                                       maxlength="80"
                                       required
                                       data-home-cta-field="primary_cta_text">
                            </div>
                            <div class="col-md-6">
                                <label for="home_cta_primary_route" class="form-label">Primary Button Destination<span class="text-danger ms-1">*</span></label>
                                <select class="form-select mb-2"
                                        id="home_cta_primary_route"
                                        data-home-cta-route-select="primary">
                                    <?php foreach ($ctaRoutes as $route): ?>
                                        <?php $value = (string)($route['value'] ?? ''); ?>
                                        <option value="<?= htmlspecialchars($value, ENT_QUOTES, 'UTF-8') ?>"<?= $value !== '' && $value === $primaryRouteValue ? ' selected' : '' ?>>
                                            <?= htmlspecialchars($route['label'] ?? $value) ?>
                                        </option>
                                    <?php endforeach; ?>
                                    <option value="__custom__"<?= $primaryRouteValue !== '' && !in_array($primaryRouteValue, array_column($ctaRoutes, 'value'), true) ? ' selected' : '' ?>>Custom URL…</option>
                                </select>
                                <input type="text"
                                       class="form-control"
                                       id="home_cta_primary_url"
                                       name="primary_cta_url"
                                       value="<?= htmlspecialchars($ctaForm['primary_cta_url'] ?? '') ?>"
                                       maxlength="255"
                                       required
                                       placeholder="/contact or https://example.com"
                                       data-home-cta-field="primary_cta_url">
                                <div class="form-text">Internal links should start with “/”. External links must include http(s).</div>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="home_cta_secondary_text" class="form-label">Secondary Button Text</label>
                                <input type="text"
                                       class="form-control"
                                       id="home_cta_secondary_text"
                                       name="secondary_cta_text"
                                       value="<?= htmlspecialchars($ctaForm['secondary_cta_text'] ?? '') ?>"
                                       maxlength="80"
                                       data-home-cta-field="secondary_cta_text">
                                <div class="form-text">Leave blank to hide the secondary button.</div>
                            </div>
                            <div class="col-md-6">
                                <label for="home_cta_secondary_route" class="form-label">Secondary Button Destination</label>
                                <select class="form-select mb-2"
                                        id="home_cta_secondary_route"
                                        data-home-cta-route-select="secondary">
                                    <?php foreach ($ctaRoutes as $route): ?>
                                        <?php $value = (string)($route['value'] ?? ''); ?>
                                        <option value="<?= htmlspecialchars($value, ENT_QUOTES, 'UTF-8') ?>"<?= $value !== '' && $value === $secondaryRouteValue ? ' selected' : '' ?>>
                                            <?= htmlspecialchars($route['label'] ?? $value) ?>
                                        </option>
                                    <?php endforeach; ?>
                                    <option value="__custom__"<?= $secondaryRouteValue !== '' && !in_array($secondaryRouteValue, array_column($ctaRoutes, 'value'), true) ? ' selected' : '' ?>>Custom URL…</option>
                                </select>
                                <input type="text"
                                       class="form-control"
                                       id="home_cta_secondary_url"
                                       name="secondary_cta_url"
                                       value="<?= htmlspecialchars($ctaForm['secondary_cta_url'] ?? '') ?>"
                                       maxlength="255"
                                       placeholder="Optional URL"
                                       data-home-cta-field="secondary_cta_url">
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-xl-5">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title mb-0">
                            <i class="bi bi-eye me-2"></i>
                            Live Preview
                        </h5>
                        <small class="text-muted">Mirrors how the CTA renders on the homepage.</small>
                    </div>
                </div>
                <div class="card-body d-flex align-items-center justify-content-center">
                    <div class="w-100">
                        <section class="cta-section bg-light border rounded p-4 text-center shadow-sm position-relative">
                            <h2 class="cta-title mb-3" data-home-cta-preview="title"><?= htmlspecialchars($ctaPreview['title'] ?? '') ?></h2>
                            <p class="cta-subtitle text-muted mb-4" data-home-cta-preview="subtitle"><?= htmlspecialchars($ctaPreview['subtitle'] ?? '') ?></p>
                            <div class="cta-actions d-flex flex-wrap justify-content-center gap-3">
                                <a href="#"
                                   class="btn btn-primary btn-lg"
                                   data-home-cta-preview-primary>
                                    <i class="bi bi-envelope me-2"></i>
                                    <span data-home-cta-preview="primary_cta_text"><?= htmlspecialchars($ctaPreview['primary_cta_text'] ?? '') ?></span>
                                </a>
                                <a href="#"
                                   class="btn btn-outline-primary btn-lg"
                                   data-home-cta-preview-secondary>
                                    <i class="bi bi-list-check me-2"></i>
                                    <span data-home-cta-preview="secondary_cta_text"><?= htmlspecialchars($ctaPreview['secondary_cta_text'] ?? '') ?></span>
                                </a>
                            </div>
                        </section>
                        <div class="mt-3 text-center small text-muted">
                            <i class="bi bi-info-circle me-1"></i>
                            Preview reflects typography and spacing used on the public site.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
