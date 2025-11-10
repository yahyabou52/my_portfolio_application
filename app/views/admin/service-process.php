<?php
$services = $services ?? [];
$servicesJson = $services_json ?? '[]';
$stepsJson = $steps_json ?? '[]';
$initialServiceId = $initial_service_id ?? null;
$routes = $process_routes ?? [];

$fetchUrl = htmlspecialchars($routes['fetch'] ?? '', ENT_QUOTES, 'UTF-8');
$storeUrl = htmlspecialchars($routes['store'] ?? '', ENT_QUOTES, 'UTF-8');
$reorderUrl = htmlspecialchars($routes['reorder'] ?? '', ENT_QUOTES, 'UTF-8');
$updateTemplate = htmlspecialchars($routes['update_template'] ?? '', ENT_QUOTES, 'UTF-8');
$deleteTemplate = htmlspecialchars($routes['delete_template'] ?? '', ENT_QUOTES, 'UTF-8');
$toggleTemplate = htmlspecialchars($routes['toggle_template'] ?? '', ENT_QUOTES, 'UTF-8');
?>

<div class="container-fluid" data-process-manager
     data-services-meta="<?= $servicesJson ?>"
     data-initial-steps="<?= $stepsJson ?>"
     data-initial-service="<?= $initialServiceId === null ? '' : (int)$initialServiceId ?>"
     data-process-fetch-url="<?= $fetchUrl ?>"
     data-process-store-url="<?= $storeUrl ?>"
     data-process-reorder-url="<?= $reorderUrl ?>"
     data-process-update-template="<?= $updateTemplate ?>"
     data-process-delete-template="<?= $deleteTemplate ?>"
     data-process-toggle-template="<?= $toggleTemplate ?>">
    <div class="row align-items-center mb-4">
        <div class="col-12 d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <h1 class="h3 mb-0">Design Process Steps</h1>
                <p class="text-muted mb-0">Control the step-by-step journey that appears on the Services page timeline.</p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <button type="button" class="btn btn-outline-secondary" data-process-refresh>
                    <i class="bi bi-arrow-clockwise me-2"></i>
                    Reload Steps
                </button>
                <button type="button" class="btn btn-primary" data-process-add>
                    <i class="bi bi-plus-lg me-2"></i>
                    Add Step
                </button>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-xl-7">
            <div class="card h-100">
                <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <div>
                        <h5 class="card-title mb-1">Process Steps</h5>
                        <small class="text-muted">Drag to reorder, toggle visibility, and edit each step.</small>
                    </div>
                    <div class="process-service-selector d-flex align-items-center gap-2">
                        <label class="fw-semibold text-muted" for="processServiceSelect">Service</label>
                        <select id="processServiceSelect" class="form-select form-select-sm" data-process-service-select>
                            <?php foreach ($services as $service): ?>
                                <?php $value = $service['id']; ?>
                                <option value="<?= $value === null ? '' : (int)$value ?>"<?= $value === $initialServiceId ? ' selected' : '' ?>>
                                    <?= htmlspecialchars($service['title'] ?? 'Service') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="card-body">
                    <ul class="list-group gap-3" data-process-list data-sortable-list></ul>
                    <div class="text-center text-muted py-5 d-none" data-process-empty>
                        <i class="bi bi-kanban fs-3 d-block mb-2"></i>
                        <p class="mb-1">No process steps yet.</p>
                        <p class="small mb-0">Add steps to showcase how you collaborate with clients.</p>
                    </div>
                </div>
                <div class="card-footer d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div class="small text-muted" data-process-feedback></div>
                    <div class="small text-muted" data-process-count></div>
                </div>
            </div>
        </div>
        <div class="col-xl-5">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0 d-flex align-items-center gap-2">
                        <i class="bi bi-flowchart"></i>
                        Live Preview
                    </h5>
                </div>
                <div class="card-body">
                    <div class="process-preview card">
                        <div class="card-body">
                            <h6 class="mb-2" data-preview-service-title>All Services</h6>
                            <p class="text-muted small mb-4" data-preview-service-description>Steps displayed on the Services page timeline.</p>
                            <ol class="process-preview-list" data-process-preview-list></ol>
                            <div class="process-preview-empty text-muted text-center py-4 d-none" data-process-preview-empty>
                                <i class="bi bi-kanban fs-3 d-block mb-2"></i>
                                <p class="mb-0">Add design steps to preview them here.</p>
                            </div>
                        </div>
                    </div>
                    <div class="small text-muted mt-3">
                        <i class="bi bi-info-circle me-2"></i>
                        Only visible steps appear publicly. Icons are optional &mdash; leave blank for a default.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Step Modal -->
<div class="modal fade" id="processStepModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form class="modal-content" data-process-form>
            <div class="modal-header">
                <h5 class="modal-title" data-process-modal-title>Add Step</h5>
                <button type="button" class="btn-close" data-process-modal-cancel aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger d-none" data-process-modal-error></div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Step Title</label>
                    <input type="text" class="form-control" maxlength="255" data-process-field="title" required>
                    <div class="form-text">Use an action-oriented title like "Discovery" or "Prototype".</div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Description</label>
                    <textarea class="form-control" rows="3" maxlength="4000" data-process-field="description"></textarea>
                    <div class="form-text">Briefly describe what happens in this step. Markdown not supported.</div>
                </div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Icon Class <span class="text-muted fw-normal">(optional)</span></label>
                        <input type="text" class="form-control" maxlength="100" placeholder="bi-lightbulb" data-process-field="icon_class">
                        <div class="form-text">Use Bootstrap Icons (<code>bi-*</code>) or Font Awesome classes.</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Step Order <span class="text-muted fw-normal">(optional)</span></label>
                        <input type="number" class="form-control" min="1" step="1" data-process-field="step_order" placeholder="Auto">
                        <div class="form-text">Leave blank to append to the end &mdash; drag to fine-tune.</div>
                    </div>
                </div>
                <div class="mt-3">
                    <label class="form-label fw-semibold">Display</label>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" role="switch" data-process-field="display" checked>
                        <label class="form-check-label">Show on the live site</label>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-process-modal-cancel>Cancel</button>
                <button type="submit" class="btn btn-primary" data-process-modal-submit>Save Step</button>
            </div>
        </form>
    </div>
</div>

<!-- Templates -->
<template id="processStepItemTemplate">
    <li class="list-group-item process-step-item d-flex align-items-start gap-3" data-process-item data-sortable-item>
        <span class="sortable-handle text-muted" data-sortable-handle title="Drag to reorder">
            <i class="bi bi-grip-vertical"></i>
        </span>
        <div class="flex-grow-1">
            <div class="d-flex justify-content-between align-items-start gap-3">
                <div class="process-step-summary">
                    <div class="process-step-icon me-2" data-process-icon>
                        <i class="bi bi-check-circle"></i>
                    </div>
                    <div>
                        <h6 class="mb-1" data-process-title>Step title</h6>
                        <p class="small text-muted mb-0" data-process-excerpt>Short description preview...</p>
                    </div>
                </div>
                <div class="text-end">
                    <span class="badge bg-secondary-subtle text-secondary d-none" data-process-hidden>Hidden</span>
                    <span class="badge bg-primary-subtle text-primary" data-process-order>#1</span>
                </div>
            </div>
        </div>
        <div class="d-flex flex-column align-items-end gap-2">
            <div class="form-check form-switch form-switch-sm">
                <input class="form-check-input" type="checkbox" role="switch" data-process-toggle>
            </div>
            <div class="btn-group btn-group-sm" role="group">
                <button type="button" class="btn btn-outline-primary" data-process-edit title="Edit step">
                    <i class="bi bi-pencil"></i>
                </button>
                <button type="button" class="btn btn-outline-danger" data-process-delete title="Delete step">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        </div>
    </li>
</template>

<template id="processStepPreviewTemplate">
    <li class="process-preview-item d-flex align-items-start gap-3" data-process-preview-item>
        <span class="process-preview-icon">
            <i class="bi bi-check-circle" data-process-preview-icon></i>
        </span>
        <div>
            <h6 class="mb-1" data-process-preview-title>Step title</h6>
            <p class="small text-muted mb-0" data-process-preview-description>Step description appears here.</p>
        </div>
    </li>
</template>
