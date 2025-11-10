<?php
$allServices = $all_services ?? [];
$selectedServices = $selected_services ?? [];
$servicesMin = isset($services_min) ? (int)$services_min : 3;
$servicesMax = isset($services_max) ? (int)$services_max : 6;

$selectedIds = array_map(function ($service) {
    return (int)($service['id'] ?? 0);
}, $selectedServices);

$availableServices = array_values(array_filter($allServices, function ($service) use ($selectedIds) {
    return !in_array((int)($service['id'] ?? 0), $selectedIds, true);
}));

$selectedCount = count($selectedServices);
$availableCount = count($availableServices);

$allServicesJson = htmlspecialchars(json_encode($allServices, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), ENT_QUOTES, 'UTF-8');
$selectedServicesJson = htmlspecialchars(json_encode($selectedServices, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), ENT_QUOTES, 'UTF-8');
?>

<div class="container-fluid">
    <div class="row align-items-center mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h3 mb-0">Services Preview</h1>
                <p class="text-muted mb-0">Choose which services appear on the homepage grid and adjust their order.</p>
            </div>
        </div>
    </div>

    <div class="row" data-services-preview-manager
         data-services-all="<?= $allServicesJson ?>"
         data-services-initial="<?= $selectedServicesJson ?>"
         data-services-min="<?= $servicesMin ?>"
         data-services-max="<?= $servicesMax ?>">
        <div class="col-xl-7">
            <form method="POST" action="<?= url('admin/services/preview') ?>" class="admin-form" data-services-form>
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title mb-0">
                                <i class="bi bi-grid me-2"></i>
                                Manage Homepage Services
                            </h5>
                            <small class="text-muted">Select between <?= $servicesMin ?> and <?= $servicesMax ?> services. Drag to reorder, toggle visibility on or off.</small>
                        </div>
                        <span class="badge bg-primary-subtle text-primary" data-services-count>
                            <?= $selectedCount ?> selected
                        </span>
                    </div>
                    <div class="card-body">
                        <div class="row g-4">
                            <div class="col-lg-6">
                                <h6 class="text-uppercase text-muted fs-sm mb-3">
                                    Available Services
                                    <span class="badge bg-secondary-subtle text-secondary ms-2" data-services-available-count><?= $availableCount ?></span>
                                </h6>
                                <div class="border rounded">
                                    <ul class="list-group list-group-flush" data-services-available-list>
                                        <?php foreach ($availableServices as $service): ?>
                                            <?php $iconClass = $service['icon'] !== '' ? $service['icon'] : 'bi-briefcase'; ?>
                                            <li class="list-group-item d-flex align-items-start gap-3 flex-wrap" data-service-id="<?= (int)$service['id'] ?>">
                                                <div class="services-manager-icon text-primary">
                                                    <i class="bi <?= htmlspecialchars($iconClass) ?>"></i>
                                                </div>
                                                <div class="flex-grow-1" style="min-width: 0;">
                                                    <div class="fw-semibold mb-1"><?= htmlspecialchars($service['title']) ?></div>
                                                    <div class="text-muted small mb-0">
                                                        <?= htmlspecialchars($service['summary'] ?? '') ?>
                                                    </div>
                                                    <?php if (($service['status'] ?? 'draft') !== 'published'): ?>
                                                        <span class="badge bg-warning-subtle text-warning mt-2">Draft</span>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="d-flex align-items-center ms-auto flex-shrink-0">
                                                    <button type="button" class="btn btn-outline-primary btn-sm" data-service-add>
                                                        <i class="bi bi-plus-lg me-1"></i>
                                                        Add
                                                    </button>
                                                </div>
                                            </li>
                                        <?php endforeach; ?>
                                        <?php if (empty($availableServices)): ?>
                                            <li class="list-group-item text-muted text-center py-4" data-services-available-empty>
                                                All services are currently selected.
                                            </li>
                                        <?php endif; ?>
                                    </ul>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <h6 class="text-uppercase text-muted fs-sm mb-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                                    <span>Homepage Selection</span>
                                    <span class="text-muted small" data-services-range-label><?= $selectedCount ?>/<?= $servicesMax ?> selected</span>
                                </h6>
                                <div class="border rounded position-relative">
                                    <ul class="list-group list-group-flush" data-services-selected-list data-sortable-list>
                                        <?php foreach ($selectedServices as $service): ?>
                                            <?php $iconClass = $service['icon'] !== '' ? $service['icon'] : 'bi-briefcase'; ?>
                                            <li class="list-group-item d-flex align-items-start gap-3 flex-wrap" data-service-item data-sortable-item data-service-id="<?= (int)$service['id'] ?>">
                                                <span class="sortable-handle text-muted" title="Drag to reorder" data-sortable-handle>
                                                    <i class="bi bi-grip-vertical"></i>
                                                </span>
                                                <div class="services-manager-icon text-primary">
                                                    <i class="bi <?= htmlspecialchars($iconClass) ?>"></i>
                                                </div>
                                                <div class="flex-grow-1" style="min-width: 0;">
                                                    <div class="fw-semibold mb-1"><?= htmlspecialchars($service['title']) ?></div>
                                                    <div class="text-muted small mb-2"><?= htmlspecialchars($service['summary'] ?? '') ?></div>
                                                    <div class="form-check form-switch form-switch-sm">
                                                        <input class="form-check-input" type="checkbox" role="switch" data-service-visible-toggle <?= !empty($service['visible']) ? 'checked' : '' ?>>
                                                        <label class="form-check-label small">Visible on homepage</label>
                                                    </div>
                                                </div>
                                                <div class="d-flex align-items-center ms-auto flex-shrink-0">
                                                    <button type="button" class="btn btn-outline-danger btn-sm" data-service-remove>
                                                        <i class="bi bi-x-lg"></i>
                                                        Remove
                                                    </button>
                                                </div>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                    <div class="text-center text-muted py-4<?= empty($selectedServices) ? '' : ' d-none' ?>" data-services-empty>
                                        <i class="bi bi-layout-text-sidebar-reverse fs-4 d-block mb-2"></i>
                                        <p class="mb-0">No services selected yet. Choose at least three to fill the homepage grid.</p>
                                    </div>
                                </div>
                                <div class="small text-muted mt-3 d-none" data-services-feedback></div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer d-flex justify-content-end gap-2">
                        <input type="hidden" name="services" value="<?= $selectedServicesJson ?>" data-services-payload>
                        <button type="button" class="btn btn-outline-secondary" data-services-cancel>
                            Cancel
                        </button>
                        <button type="submit" class="btn btn-primary" data-services-save>
                            <i class="bi bi-save me-2"></i>
                            Save Homepage Services
                        </button>
                    </div>
                </div>
            </form>
        </div>
        <div class="col-xl-5">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-columns-gap me-2"></i>
                        Homepage Preview
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3" data-services-preview>
                        <?php if (!empty($selectedServices)): ?>
                            <?php $visiblePreview = array_filter($selectedServices, function ($service) { return !empty($service['visible']); }); ?>
                            <?php foreach ($visiblePreview as $service): ?>
                                <?php $iconClass = $service['icon'] !== '' ? $service['icon'] : 'bi-briefcase'; ?>
                                <div class="col-sm-6" data-services-preview-item>
                                    <div class="card h-100 shadow-sm">
                                        <div class="card-body">
                                            <div class="services-preview-icon text-primary mb-3">
                                                <i class="bi <?= htmlspecialchars($iconClass) ?>"></i>
                                            </div>
                                            <h6 class="card-title mb-2"><?= htmlspecialchars($service['title']) ?></h6>
                                            <p class="card-text small text-muted mb-0"><?= htmlspecialchars($service['summary'] ?? '') ?></p>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            <?php $visibleCount = count($visiblePreview); ?>
                            <?php if ($visibleCount < $servicesMin): ?>
                                <?php for ($i = 0; $i < $servicesMin - $visibleCount; $i++): ?>
                                    <div class="col-sm-6" data-services-placeholder>
                                        <div class="card h-100 shadow-sm placeholder-glow">
                                            <div class="card-body">
                                                <div class="placeholder rounded-circle mb-3" style="width: 48px; height: 48px;"></div>
                                                <span class="placeholder col-8 mb-2"></span>
                                                <span class="placeholder col-10"></span>
                                            </div>
                                        </div>
                                    </div>
                                <?php endfor; ?>
                            <?php endif; ?>
                        <?php else: ?>
                            <?php for ($i = 0; $i < $servicesMin; $i++): ?>
                                <div class="col-sm-6" data-services-placeholder>
                                    <div class="card h-100 shadow-sm placeholder-glow">
                                        <div class="card-body">
                                            <div class="placeholder rounded-circle mb-3" style="width: 48px; height: 48px;"></div>
                                            <span class="placeholder col-8 mb-2"></span>
                                            <span class="placeholder col-10"></span>
                                        </div>
                                    </div>
                                </div>
                            <?php endfor; ?>
                        <?php endif; ?>
                    </div>
                    <div class="small text-muted mt-3">
                        <i class="bi bi-info-circle me-1"></i>
                        Preview mirrors the homepage services grid. Keep at least three visible cards for balance.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<template id="servicesAvailableTemplate">
    <li class="list-group-item d-flex align-items-start gap-3 flex-wrap" data-service-id>
        <div class="services-manager-icon text-primary">
            <i class="bi" data-service-icon></i>
        </div>
        <div class="flex-grow-1" style="min-width: 0;">
            <div class="fw-semibold mb-1" data-service-title></div>
            <div class="text-muted small mb-0" data-service-summary></div>
            <span class="badge bg-warning-subtle text-warning mt-2 d-none" data-service-status>Draft</span>
        </div>
        <div class="d-flex align-items-center ms-auto flex-shrink-0">
            <button type="button" class="btn btn-outline-primary btn-sm" data-service-add>
                <i class="bi bi-plus-lg me-1"></i>
                Add
            </button>
        </div>
    </li>
</template>

<template id="servicesSelectedTemplate">
    <li class="list-group-item d-flex align-items-start gap-3 flex-wrap" data-service-item data-sortable-item>
        <span class="sortable-handle text-muted" title="Drag to reorder" data-sortable-handle>
            <i class="bi bi-grip-vertical"></i>
        </span>
        <div class="services-manager-icon text-primary">
            <i class="bi" data-service-icon></i>
        </div>
        <div class="flex-grow-1" style="min-width: 0;">
            <div class="fw-semibold mb-1" data-service-title></div>
            <div class="text-muted small mb-2" data-service-summary></div>
            <div class="form-check form-switch form-switch-sm">
                <input class="form-check-input" type="checkbox" role="switch" data-service-visible-toggle>
                <label class="form-check-label small">Visible on homepage</label>
            </div>
        </div>
        <div class="d-flex align-items-center ms-auto flex-shrink-0">
            <button type="button" class="btn btn-outline-danger btn-sm" data-service-remove>
                <i class="bi bi-x-lg"></i>
                Remove
            </button>
        </div>
    </li>
</template>

<template id="servicesPreviewTemplate">
    <div class="col-sm-6" data-services-preview-item>
        <div class="card h-100 shadow-sm">
            <div class="card-body">
                <div class="services-preview-icon text-primary mb-3">
                    <i class="bi" data-service-icon></i>
                </div>
                <h6 class="card-title mb-2" data-service-title></h6>
                <p class="card-text small text-muted mb-0" data-service-summary></p>
            </div>
        </div>
    </div>
</template>

<template id="servicesPlaceholderTemplate">
    <div class="col-sm-6" data-services-placeholder>
        <div class="card h-100 shadow-sm placeholder-glow">
            <div class="card-body">
                <div class="placeholder rounded-circle mb-3" style="width: 48px; height: 48px;"></div>
                <span class="placeholder col-8 mb-2"></span>
                <span class="placeholder col-10"></span>
            </div>
        </div>
    </div>
</template>
