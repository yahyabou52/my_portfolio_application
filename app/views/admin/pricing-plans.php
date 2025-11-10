<?php
$plansJson = $plans_json ?? '[]';
$routesJson = $routes_json ?? '{}';
$stats = $stats ?? ['total' => 0, 'visible' => 0, 'hidden' => 0, 'highlighted' => 0, 'highlighted_id' => null];
?>

<div class="container-fluid" data-pricing-plan-manager data-pricing-plan-initial="<?= $plansJson ?>" data-pricing-plan-routes="<?= $routesJson ?>">
    <div class="row align-items-center mb-4">
        <div class="col-lg-7">
            <h1 class="h3 mb-1">Pricing Plans</h1>
            <p class="text-muted mb-0">Manage the Services page pricing grid with drag-and-drop sorting, live previews, and highlight controls.</p>
        </div>
        <div class="col-lg-5 d-flex justify-content-lg-end gap-2 mt-3 mt-lg-0">
            <button type="button" class="btn btn-outline-secondary" data-pricing-plan-refresh>
                <i class="bi bi-arrow-clockwise me-2"></i>
                Refresh
            </button>
            <button type="button" class="btn btn-primary" data-pricing-plan-add>
                <i class="bi bi-plus-lg me-2"></i>
                Add Plan
            </button>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card h-100 shadow-sm">
                <div class="card-body py-3 text-center">
                    <p class="text-muted text-uppercase small mb-1">Total Plans</p>
                    <p class="display-6 mb-0" data-pricing-plan-stat="total"><?= (int)$stats['total'] ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card h-100 shadow-sm">
                <div class="card-body py-3 text-center">
                    <p class="text-muted text-uppercase small mb-1">Visible</p>
                    <p class="display-6 mb-0 text-success" data-pricing-plan-stat="visible"><?= (int)$stats['visible'] ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card h-100 shadow-sm">
                <div class="card-body py-3 text-center">
                    <p class="text-muted text-uppercase small mb-1">Hidden</p>
                    <p class="display-6 mb-0 text-muted" data-pricing-plan-stat="hidden"><?= (int)$stats['hidden'] ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card h-100 shadow-sm">
                <div class="card-body py-3 text-center">
                    <p class="text-muted text-uppercase small mb-1">Highlighted</p>
                    <p class="display-6 mb-0 text-primary" data-pricing-plan-stat="highlighted"><?= (int)$stats['highlighted'] ?></p>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-xl-8">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <h5 class="card-title mb-1">Plan Ordering &amp; Highlighting</h5>
                        <small class="text-muted">Drag to reorder and choose which plan carries the highlight badge.</small>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-primary-subtle text-primary" data-pricing-plan-highlight-summary>
                            <i class="bi bi-star-fill me-1"></i>
                            Highlighted plan updates the accent styling on the Services page.
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    <ul class="list-group gap-3" data-pricing-plan-list data-sortable-list></ul>
                    <div class="text-center text-muted py-5 d-none" data-pricing-plan-empty>
                        <i class="bi bi-ui-checks-grid fs-3 d-block mb-2"></i>
                        <p class="mb-1">No pricing plans yet.</p>
                        <p class="small mb-0">Add a plan to start building the pricing grid.</p>
                    </div>
                    <div class="small text-muted mt-3" data-pricing-plan-feedback></div>
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-1 d-flex align-items-center gap-2">
                        <i class="bi bi-columns-gap"></i>
                        Live Preview
                    </h5>
                    <small class="text-muted">Mirrors the public Services page layout.</small>
                </div>
                <div class="card-body">
                    <div class="pricing-preview-grid" data-pricing-plan-preview></div>
                    <div class="text-center text-muted py-5" data-pricing-plan-preview-empty>
                        <i class="bi bi-grid-1x2 fs-3 d-block mb-2"></i>
                        <p class="mb-1">Preview updates as you edit plans.</p>
                        <p class="small mb-0">Highlighted plans appear with the accent background.</p>
                    </div>
                    <div class="small text-muted mt-3">
                        <i class="bi bi-info-circle me-2"></i>
                        Set plan visibility from the form — hidden plans stay off the Services page.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Pricing Plan Modal -->
<div class="modal fade" id="pricingPlanModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <form class="modal-content" data-pricing-plan-form>
            <div class="modal-header">
                <h5 class="modal-title" data-pricing-plan-modal-title>Add Pricing Plan</h5>
                <button type="button" class="btn-close" data-pricing-plan-modal-cancel aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger d-none" data-pricing-plan-modal-error></div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Plan Title</label>
                        <input type="text" class="form-control" data-pricing-field="title" maxlength="255" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Subtitle <span class="text-muted fw-normal">(optional)</span></label>
                        <input type="text" class="form-control" data-pricing-field="subtitle" maxlength="255">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Price Amount <span class="text-muted fw-normal">(optional)</span></label>
                        <input type="number" class="form-control" data-pricing-field="price_amount" step="0.01" min="0" placeholder="2499">
                        <div class="form-text">Leave blank to hide the price chip.</div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Price Period <span class="text-muted fw-normal">(optional)</span></label>
                        <input type="text" class="form-control" data-pricing-field="price_period" maxlength="50" placeholder="per project">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Badge Text <span class="text-muted fw-normal">(optional)</span></label>
                        <input type="text" class="form-control" data-pricing-field="badge_text" maxlength="120" placeholder="Popular Choice">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">CTA Label</label>
                        <input type="text" class="form-control" data-pricing-field="cta_label" maxlength="120" placeholder="Start Project" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">CTA URL</label>
                        <input type="text" class="form-control" data-pricing-field="cta_url" maxlength="255" placeholder="/contact">
                        <div class="form-text">Accepts relative or absolute URLs.</div>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Features <span class="text-muted fw-normal">(one per line)</span></label>
                        <textarea class="form-control" rows="5" data-pricing-field="features" maxlength="4000" placeholder="Kickoff strategy workshop&#10;Research insights &amp; personas&#10;High-fidelity UI screens&#10;Interactive prototypes"></textarea>
                    </div>
                    <div class="col-12">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" data-pricing-field="visible" checked>
                            <label class="form-check-label">Display this plan on the Services page</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-pricing-plan-modal-cancel>Cancel</button>
                <button type="submit" class="btn btn-primary" data-pricing-plan-modal-submit>Save Plan</button>
            </div>
        </form>
    </div>
</div>

<!-- Templates -->
<template id="pricingPlanListItemTemplate">
    <li class="list-group-item pricing-plan-item d-flex align-items-start gap-3" data-plan-item data-sortable-item>
        <span class="sortable-handle text-muted" data-sortable-handle title="Drag to reorder">
            <i class="bi bi-grip-vertical"></i>
        </span>
        <div class="flex-grow-1">
            <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <h6 class="mb-0" data-plan-title>Plan Title</h6>
                        <span class="badge bg-primary d-none" data-plan-highlight-badge><i class="bi bi-star-fill me-1"></i>Highlighted</span>
                        <span class="badge bg-secondary d-none" data-plan-hidden-badge>Hidden</span>
                    </div>
                    <p class="text-muted small mb-2" data-plan-subtitle>Subtitle appears here.</p>
                    <div class="d-flex flex-wrap gap-2 small text-muted" data-plan-feature-summary></div>
                </div>
                <div class="text-end">
                    <div class="fw-semibold" data-plan-price></div>
                    <small class="text-muted" data-plan-badge></small>
                </div>
            </div>
        </div>
        <div class="d-flex flex-column align-items-end gap-2">
            <div class="btn-group btn-group-sm" role="group">
                <button type="button" class="btn btn-outline-warning" data-plan-highlight title="Highlight plan">
                    <i class="bi bi-star"></i>
                </button>
                <button type="button" class="btn btn-outline-primary" data-plan-edit title="Edit plan">
                    <i class="bi bi-pencil"></i>
                </button>
                <button type="button" class="btn btn-outline-danger" data-plan-delete title="Delete plan">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        </div>
    </li>
</template>

<template id="pricingPlanPreviewTemplate">
    <div class="pricing-preview-card" data-preview-plan>
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h6 class="mb-1" data-preview-title>Plan Title</h6>
                        <p class="text-muted small mb-0" data-preview-subtitle>Subtitle preview.</p>
                    </div>
                    <div class="text-end" data-preview-pricing>
                        <div class="fs-5 fw-semibold" data-preview-price></div>
                        <div class="text-muted small" data-preview-period></div>
                    </div>
                </div>
                <div class="badge bg-primary-subtle text-primary d-inline-flex align-items-center gap-1 mb-3 d-none" data-preview-highlight>
                    <i class="bi bi-star-fill"></i>
                    Highlighted
                </div>
                <div class="badge bg-secondary-subtle text-secondary d-inline-flex align-items-center gap-1 mb-3 d-none" data-preview-hidden>
                    <i class="bi bi-eye-slash"></i>
                    Hidden
                </div>
                <div class="badge bg-info-subtle text-info mb-3 d-none" data-preview-badge></div>
                <ul class="list-unstyled pricing-preview-features mb-4" data-preview-features></ul>
                <a href="#" class="btn btn-outline-primary w-100" data-preview-cta>Start Project</a>
            </div>
        </div>
    </div>
</template>
