<?php
$services = $services ?? [];
$initialPayload = htmlspecialchars(json_encode($services, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), ENT_QUOTES, 'UTF-8');
$formErrors = [];

if (isset($this) && method_exists($this, 'hasFlash') && $this->hasFlash('form_errors')) {
    $errorFlash = $this->getFlash('form_errors');
    if (is_array($errorFlash)) {
        $formErrors = $errorFlash;
    } elseif (is_string($errorFlash) && $errorFlash !== '') {
        $formErrors = [$errorFlash];
    }
}
?>

<div class="container-fluid" data-services-manager data-services-initial="<?= $initialPayload ?>">
    <div class="row align-items-center mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h1 class="h3 mb-0">Services Page</h1>
                <p class="text-muted mb-0">Curate the hero service cards with drag-and-drop ordering and live preview.</p>
            </div>
            <div>
                <button type="button" class="btn btn-outline-primary" data-services-add>
                    <i class="bi bi-plus-lg me-2"></i>
                    Add Service
                </button>
            </div>
        </div>
    </div>

    <?php if (!empty($formErrors)): ?>
        <div class="alert alert-danger" role="alert">
            <h6 class="alert-heading mb-2">We ran into a few validation issues:</h6>
            <ul class="mb-0">
                <?php foreach ($formErrors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="row g-4">
        <div class="col-xl-8">
            <form method="POST" data-services-form class="h-100 d-flex flex-column">
                <div class="card flex-grow-1">
                    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-3">
                        <div>
                            <h5 class="card-title mb-1">Main Services List</h5>
                            <small class="text-muted">Reorder, toggle visibility, and fine-tune card content.</small>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-outline-secondary" data-services-cancel>Cancel</button>
                            <button type="submit" class="btn btn-primary" data-services-save>
                                <i class="bi bi-save me-2"></i>
                                Save Changes
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <ul class="list-group gap-3" data-services-list data-sortable-list></ul>
                        <div class="text-center text-muted py-5 d-none" data-services-empty>
                            <i class="bi bi-stack fs-3 d-block mb-2"></i>
                            <p class="mb-1">No services added yet.</p>
                            <p class="small mb-0">Use the <strong>Add Service</strong> button to start building your offering.</p>
                        </div>
                        <div class="small text-muted mt-3" data-services-feedback></div>
                    </div>
                    <div class="card-footer">
                        <input type="hidden" name="services_structure" data-services-payload value="<?= $initialPayload ?>">
                    </div>
                </div>
            </form>
        </div>
        <div class="col-xl-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0 d-flex align-items-center gap-2">
                        <i class="bi bi-layout-text-sidebar"></i>
                        Live Preview
                    </h5>
                </div>
                <div class="card-body">
                    <div class="services-preview-grid" data-services-preview></div>
                    <div class="text-center text-muted py-5" data-preview-empty>
                        <i class="bi bi-columns-gap fs-3 d-block mb-2"></i>
                        <p class="mb-1">Preview updates as you add services.</p>
                        <p class="small mb-0">Hidden cards appear dimmed to reflect the published page.</p>
                    </div>
                    <div class="small text-muted mt-3">
                        <i class="bi bi-info-circle me-2"></i>
                        Preview mirrors the Services page hero layout. Features render as bullet points and pricing chips when provided.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Service Modal -->
<div class="modal fade" id="serviceModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <form class="modal-content" data-service-form>
            <div class="modal-header">
                <h5 class="modal-title" data-service-modal-title>Add Service</h5>
                <button type="button" class="btn-close" data-service-modal-cancel aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger d-none" data-service-modal-error></div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Icon Class</label>
                        <input type="text" class="form-control" data-service-field="icon" maxlength="120" placeholder="bi-lightning-charge">
                        <div class="form-text">Use Bootstrap Icons (<code>bi-*</code>) or Font Awesome classes.</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Visibility</label>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" data-service-field="visible" checked>
                            <label class="form-check-label">Display this service on the website</label>
                        </div>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Title</label>
                        <input type="text" class="form-control" data-service-field="title" maxlength="200" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Description</label>
                        <textarea class="form-control" rows="3" data-service-field="description" maxlength="2000" required></textarea>
                        <div class="form-text">Keep it concise &mdash; this text appears under the service title.</div>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Features <span class="text-muted fw-normal">(one per line)</span></label>
                        <textarea class="form-control" rows="4" data-service-field="features" maxlength="2000" placeholder="Discovery workshop&#10;User research synthesis&#10;Interactive prototype validation"></textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Price Label <span class="text-muted fw-normal">(optional)</span></label>
                        <input type="text" class="form-control" data-service-field="price_label" maxlength="120" placeholder="Starting from">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Price Amount <span class="text-muted fw-normal">(optional)</span></label>
                        <input type="number" class="form-control" data-service-field="price_amount" step="0.01" min="0" placeholder="2500">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-service-modal-cancel>Cancel</button>
                <button type="submit" class="btn btn-primary">Save Service</button>
            </div>
        </form>
    </div>
</div>

<!-- Templates -->
<template id="serviceListItemTemplate">
    <li class="list-group-item services-manager-item d-flex align-items-start gap-3" data-service-item data-sortable-item>
        <span class="sortable-handle text-muted" data-sortable-handle title="Drag to reorder">
            <i class="bi bi-grip-vertical"></i>
        </span>
        <div class="services-manager-icon">
            <span class="services-icon-badge">
                <i class="bi bi-lightning-charge" data-service-icon-preview></i>
            </span>
        </div>
        <div class="flex-grow-1">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                <div>
                    <h6 class="mb-1" data-service-title>Service Title</h6>
                    <p class="text-muted small mb-2" data-service-description>Short service description.</p>
                    <div class="d-flex flex-wrap gap-2 small text-muted" data-service-feature-summary></div>
                </div>
                <div class="text-end" data-service-pricing>
                    <span class="badge bg-primary-subtle text-primary d-none" data-service-price></span>
                    <span class="badge bg-secondary-subtle text-secondary d-none" data-service-hidden>Hidden</span>
                </div>
            </div>
        </div>
        <div class="d-flex flex-column align-items-end gap-2">
            <div class="form-check form-switch form-switch-sm">
                <input class="form-check-input" type="checkbox" role="switch" data-service-visible-toggle>
            </div>
            <div class="btn-group btn-group-sm" role="group">
                <button type="button" class="btn btn-outline-primary" data-service-edit title="Edit service">
                    <i class="bi bi-pencil"></i>
                </button>
                <button type="button" class="btn btn-outline-danger" data-service-delete title="Delete service">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        </div>
    </li>
</template>

<template id="servicePreviewTemplate">
    <div class="service-preview-card" data-preview-service>
        <div class="card h-100">
            <div class="card-body">
                <div class="service-preview-icon">
                    <i class="bi bi-lightning-charge" data-preview-icon></i>
                </div>
                <h6 class="mt-3 mb-2" data-preview-title>Service Title</h6>
                <p class="text-muted small" data-preview-description>Service summary appears here.</p>
                <ul class="service-preview-features" data-preview-features></ul>
                <div class="service-preview-price d-none" data-preview-price></div>
                <span class="badge bg-secondary-subtle text-secondary d-none mt-3" data-preview-hidden-badge>Hidden</span>
            </div>
        </div>
    </div>
</template>
