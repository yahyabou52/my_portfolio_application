<?php
$services = $services ?? [];
$servicesJson = $services_json ?? '[]';
$featuresJson = $features_json ?? '[]';
$initialServiceId = (int)($initial_service_id ?? 0);
$routes = $feature_routes ?? [];

$fetchUrl = htmlspecialchars($routes['fetch'] ?? '', ENT_QUOTES, 'UTF-8');
$storeUrl = htmlspecialchars($routes['store'] ?? '', ENT_QUOTES, 'UTF-8');
$reorderUrl = htmlspecialchars($routes['reorder'] ?? '', ENT_QUOTES, 'UTF-8');
$updateTemplate = htmlspecialchars($routes['update_template'] ?? '', ENT_QUOTES, 'UTF-8');
$deleteTemplate = htmlspecialchars($routes['delete_template'] ?? '', ENT_QUOTES, 'UTF-8');
$toggleTemplate = htmlspecialchars($routes['toggle_template'] ?? '', ENT_QUOTES, 'UTF-8');
?>

<div class="container-fluid" data-service-features-manager
     data-services-meta="<?= $servicesJson ?>"
     data-initial-features="<?= $featuresJson ?>"
     data-initial-service="<?= $initialServiceId ?>"
     data-feature-fetch-url="<?= $fetchUrl ?>"
     data-feature-store-url="<?= $storeUrl ?>"
     data-feature-reorder-url="<?= $reorderUrl ?>"
     data-feature-update-template="<?= $updateTemplate ?>"
     data-feature-delete-template="<?= $deleteTemplate ?>"
     data-feature-toggle-template="<?= $toggleTemplate ?>">
    <div class="row align-items-center mb-4">
        <div class="col-12 d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <h1 class="h3 mb-0">Service Feature Bullets</h1>
                <p class="text-muted mb-0">Fine-tune the value props that appear beneath each services card.</p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <button type="button" class="btn btn-outline-secondary" data-feature-refresh>
                    <i class="bi bi-arrow-clockwise me-2"></i>
                    Reload Features
                </button>
                <button type="button" class="btn btn-primary" data-feature-add>
                    <i class="bi bi-plus-lg me-2"></i>
                    Add Feature
                </button>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-xl-7">
            <div class="card h-100">
                <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <div>
                        <h5 class="card-title mb-1">Feature List</h5>
                        <small class="text-muted">Drag to reorder, toggle visibility, and edit the details for each bullet.</small>
                    </div>
                    <div class="feature-service-selector d-flex align-items-center gap-2">
                        <label class="fw-semibold text-muted" for="serviceFeatureServiceSelect">Service</label>
                        <select id="serviceFeatureServiceSelect" class="form-select form-select-sm" data-feature-service-select>
                            <?php foreach ($services as $service): ?>
                                <?php $serviceId = (int)($service['id'] ?? 0); ?>
                                <option value="<?= $serviceId ?>"<?= $serviceId === $initialServiceId ? ' selected' : '' ?>>
                                    <?= htmlspecialchars($service['title'] ?? 'Untitled Service') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="card-body">
                    <ul class="list-group gap-3" data-feature-list data-sortable-list></ul>
                    <div class="text-center text-muted py-5 d-none" data-feature-empty>
                        <i class="bi bi-list-ul fs-3 d-block mb-2"></i>
                        <p class="mb-1">No feature bullets yet.</p>
                        <p class="small mb-0">Add feature highlights to reinforce the service value proposition.</p>
                    </div>
                </div>
                <div class="card-footer d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div class="small text-muted" data-feature-feedback></div>
                    <div class="small text-muted" data-feature-count></div>
                </div>
            </div>
        </div>
        <div class="col-xl-5">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0 d-flex align-items-center gap-2">
                        <i class="bi bi-window-stack"></i>
                        Live Preview
                    </h5>
                </div>
                <div class="card-body">
                    <div class="service-feature-preview card">
                        <div class="card-body">
                            <div class="service-feature-preview-icon mb-3">
                                <i class="bi bi-stars" data-preview-service-icon></i>
                            </div>
                            <h6 class="mb-2" data-preview-service-title>Service Title</h6>
                            <p class="text-muted small mb-4" data-preview-service-description>Service description copy appears here.</p>
                            <ul class="service-feature-preview-list" data-feature-preview-list></ul>
                            <div class="service-feature-preview-empty text-muted text-center py-4 d-none" data-feature-preview-empty>
                                <i class="bi bi-list-check fs-3 d-block mb-2"></i>
                                <p class="mb-0">Add feature bullets to see them here.</p>
                            </div>
                        </div>
                    </div>
                    <div class="small text-muted mt-3">
                        <i class="bi bi-info-circle me-2"></i>
                        Only visible bullets publish to the services page. Icons are optional &mdash; they fall back to a simple checkmark.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Feature Modal -->
<div class="modal fade" id="serviceFeatureModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form class="modal-content" data-feature-form>
            <div class="modal-header">
                <h5 class="modal-title" data-feature-modal-title>Add Feature</h5>
                <button type="button" class="btn-close" data-feature-modal-cancel aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger d-none" data-feature-modal-error></div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Feature Text</label>
                    <textarea class="form-control" rows="3" maxlength="255" data-feature-field="feature_text" required></textarea>
                    <div class="form-text">Keep it concise &mdash; this text appears as a bullet under the selected service.</div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Icon Class <span class="text-muted fw-normal">(optional)</span></label>
                    <input type="text" class="form-control" maxlength="100" placeholder="bi-check2-circle" data-feature-field="icon_class">
                    <div class="form-text">Use Bootstrap Icons (<code>bi-*</code>) or Font Awesome classes. Leave blank to show a default check.</div>
                </div>
                <div class="row g-3 align-items-center">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Sort Order <span class="text-muted fw-normal">(optional)</span></label>
                        <input type="number" class="form-control" min="1" step="1" data-feature-field="sort_order" placeholder="Auto">
                        <div class="form-text">Leave blank to append to the end &mdash; you can always drag to reorder.</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Display</label>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" data-feature-field="display" checked>
                            <label class="form-check-label">Show on the live site</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-feature-modal-cancel>Cancel</button>
                <button type="submit" class="btn btn-primary" data-feature-modal-submit>Save Feature</button>
            </div>
        </form>
    </div>
</div>

<!-- Templates -->
<template id="serviceFeatureItemTemplate">
    <li class="list-group-item service-feature-item d-flex align-items-start gap-3" data-feature-item data-sortable-item>
        <span class="sortable-handle text-muted" data-sortable-handle title="Drag to reorder">
            <i class="bi bi-grip-vertical"></i>
        </span>
        <div class="flex-grow-1">
            <div class="d-flex justify-content-between align-items-start gap-3">
                <div>
                    <h6 class="mb-1" data-feature-text>Feature highlight</h6>
                    <div class="small text-muted" data-feature-icon-label>No custom icon</div>
                </div>
                <div class="text-end">
                    <span class="badge bg-secondary-subtle text-secondary d-none" data-feature-hidden>Hidden</span>
                    <span class="badge bg-primary-subtle text-primary" data-feature-order>#1</span>
                </div>
            </div>
        </div>
        <div class="d-flex flex-column align-items-end gap-2">
            <div class="form-check form-switch form-switch-sm">
                <input class="form-check-input" type="checkbox" role="switch" data-feature-toggle>
            </div>
            <div class="btn-group btn-group-sm" role="group">
                <button type="button" class="btn btn-outline-primary" data-feature-edit title="Edit feature">
                    <i class="bi bi-pencil"></i>
                </button>
                <button type="button" class="btn btn-outline-danger" data-feature-delete title="Delete feature">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        </div>
    </li>
</template>

<template id="serviceFeaturePreviewItemTemplate">
    <li class="service-feature-preview-item d-flex align-items-start gap-2" data-preview-feature>
        <span class="service-feature-preview-icon" data-preview-feature-icon>
            <i class="bi bi-check2"></i>
        </span>
        <span data-preview-feature-text>Feature bullet</span>
    </li>
</template>
