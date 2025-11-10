<?php
$projects = $projects ?? [];
$featuredProjects = $featured_projects ?? [];
$featuredIds = $featured_ids ?? [];

$normalizeProject = function ($project) {
    $imagePath = $project['main_image_path'] ?? ($project['image_url'] ?? '');
    $imagePath = is_string($imagePath) ? trim($imagePath) : '';
    $imageUrl = $imagePath !== '' ? media_url($imagePath) : asset('images/projects/default.jpg');

    return [
        'id' => (int)($project['id'] ?? 0),
        'title' => $project['title'] ?? 'Untitled Project',
        'slug' => $project['slug'] ?? '',
        'short_description' => $project['short_description'] ?? '',
        'category' => $project['category'] ?? '',
        'status' => $project['status'] ?? 'draft',
        'main_image_url' => $imageUrl,
        'featured_sort_order' => (int)($project['featured_sort_order'] ?? 0),
        'sort_order' => (int)($project['sort_order'] ?? 0)
    ];
};

$allProjectItems = array_values(array_map($normalizeProject, $projects));
$featuredItems = array_values(array_map($normalizeProject, $featuredProjects));

usort($featuredItems, function ($a, $b) {
    $orderA = $a['featured_sort_order'] ?? 0;
    $orderB = $b['featured_sort_order'] ?? 0;
    if ($orderA === $orderB) {
        return ($a['sort_order'] ?? 0) <=> ($b['sort_order'] ?? 0);
    }
    return $orderA <=> $orderB;
});

$featuredIds = array_map(function ($item) {
    return (int)$item['id'];
}, $featuredItems);

$availableItems = array_values(array_filter($allProjectItems, function ($project) use ($featuredIds) {
    return !in_array($project['id'], $featuredIds, true);
}));

$featuredJson = htmlspecialchars(json_encode($featuredItems, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), ENT_QUOTES, 'UTF-8');
$projectsJson = htmlspecialchars(json_encode($allProjectItems, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), ENT_QUOTES, 'UTF-8');
$featuredOrderValue = implode(',', array_map(function ($project) {
    return (string)$project['id'];
}, $featuredItems));

$minFeatured = 3;
$maxFeatured = 6;
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">Featured Work</h1>
                    <p class="text-muted mb-0">Choose up to six projects to highlight on the homepage and arrange their display order.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row" data-featured-work-manager
         data-featured-projects="<?= $projectsJson ?>"
         data-featured-initial="<?= $featuredJson ?>"
        data-featured-default-image="<?= asset('images/projects/default.jpg') ?>"
         data-featured-min="<?= $minFeatured ?>"
         data-featured-max="<?= $maxFeatured ?>">
        <div class="col-xl-7">
            <form method="POST" action="<?= url('admin/featured-projects') ?>" class="admin-form" data-featured-form data-auto-loading="false">
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title mb-0">
                                <i class="bi bi-stars me-2"></i>
                                Manage Featured Projects
                            </h5>
                            <small class="text-muted">Select between <?= $minFeatured ?> and <?= $maxFeatured ?> projects. Drag to reorder.</small>
                        </div>
                        <span class="badge bg-primary-subtle text-primary" data-featured-count>
                            <?= count($featuredItems) ?> selected
                        </span>
                    </div>
                    <div class="card-body">
                        <div class="row g-4">
                            <div class="col-lg-6">
                                <h6 class="text-uppercase text-muted fs-sm mb-3">
                                    Available Projects
                                    <span class="badge bg-secondary-subtle text-secondary ms-2" data-featured-available-count><?= count($availableItems) ?></span>
                                </h6>
                                <div class="border rounded">
                                    <ul class="list-group list-group-flush" data-featured-available-list>
                                        <?php foreach ($availableItems as $project): ?>
                                            <li class="list-group-item d-flex align-items-start gap-3 flex-wrap" data-project-id="<?= (int)$project['id'] ?>">
                                                <div class="flex-grow-1" style="min-width: 0;">
                                                    <div class="fw-semibold mb-1"><?= htmlspecialchars($project['title']) ?></div>
                                                    <div class="text-muted small mb-0">
                                                        <?= htmlspecialchars($project['short_description'] ?: 'No short description available.') ?>
                                                    </div>
                                                    <?php if (($project['status'] ?? 'draft') !== 'published'): ?>
                                                        <span class="badge bg-warning-subtle text-warning mt-2">Draft</span>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="d-flex align-items-center ms-auto flex-shrink-0">
                                                    <button type="button" class="btn btn-outline-primary btn-sm" data-featured-add>
                                                        <i class="bi bi-plus-lg me-1"></i>
                                                        Add
                                                    </button>
                                                </div>
                                            </li>
                                        <?php endforeach; ?>
                                        <?php if (empty($availableItems)): ?>
                                            <li class="list-group-item text-muted text-center py-4" data-featured-available-empty>
                                                All projects are currently featured.
                                            </li>
                                        <?php endif; ?>
                                    </ul>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <h6 class="text-uppercase text-muted fs-sm mb-3 d-flex justify-content-between">
                                    <span>Featured List</span>
                                    <span class="text-muted small" data-featured-range-label><?= count($featuredItems) ?>/<?= $maxFeatured ?> selected</span>
                                </h6>
                                <div class="border rounded position-relative">
                                    <ul class="list-group list-group-flush" data-featured-list data-sortable-list>
                                        <?php foreach ($featuredItems as $project): ?>
                                            <li class="list-group-item d-flex align-items-start gap-3 flex-wrap" data-featured-item data-sortable-item data-featured-id="<?= (int)$project['id'] ?>">
                                                <span class="sortable-handle text-muted" title="Drag to reorder" data-sortable-handle>
                                                    <i class="bi bi-grip-vertical"></i>
                                                </span>
                                                <div class="flex-grow-1" style="min-width: 0;">
                                                    <div class="fw-semibold mb-1"><?= htmlspecialchars($project['title']) ?></div>
                                                    <div class="text-muted small mb-0">
                                                        <?= htmlspecialchars($project['short_description'] ?: 'No short description available.') ?>
                                                    </div>
                                                </div>
                                                <div class="d-flex align-items-center">
                                                    <button type="button" class="btn btn-outline-danger btn-sm" data-featured-remove>
                                                        <i class="bi bi-x-lg"></i>
                                                        Remove
                                                    </button>
                                                </div>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                    <div class="text-center text-muted py-4<?= empty($featuredItems) ? '' : ' d-none' ?>" data-featured-empty>
                                        <i class="bi bi-layout-wtf fs-4 d-block mb-2"></i>
                                        <p class="mb-0">No projects selected yet. Choose up to six to feature.</p>
                                    </div>
                                </div>
                                <div class="small text-muted mt-3 d-none" data-featured-feedback></div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer d-flex justify-content-end gap-2">
                        <input type="hidden" name="featured" value="<?= htmlspecialchars($featuredOrderValue) ?>" data-featured-order-input>
                        <button type="button" class="btn btn-outline-secondary" data-featured-cancel>
                            Cancel
                        </button>
                        <button type="submit" class="btn btn-primary" data-featured-save>
                            <i class="bi bi-save me-2"></i>
                            Save Featured Projects
                        </button>
                    </div>
                </div>
            </form>
        </div>
        <div class="col-xl-5">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-grid-3x3-gap me-2"></i>
                        Homepage Preview
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3" data-featured-preview>
                        <?php if (!empty($featuredItems)): ?>
                            <?php foreach ($featuredItems as $project): ?>
                                <div class="col-sm-6 col-lg-4" data-featured-preview-item>
                                    <div class="card h-100 shadow-sm">
                                        <div class="ratio ratio-4x3">
                                            <img src="<?= htmlspecialchars($project['main_image_url']) ?>" alt="<?= htmlspecialchars($project['title']) ?>" class="rounded-top w-100 h-100" style="object-fit: cover;">
                                        </div>
                                        <div class="card-body">
                                            <h6 class="card-title mb-2"><?= htmlspecialchars($project['title']) ?></h6>
                                            <p class="card-text small text-muted mb-0">
                                                <?= htmlspecialchars($project['short_description'] ?: 'Featured project summary.') ?>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <?php for ($i = 0; $i < $minFeatured; $i++): ?>
                                <div class="col-sm-6 col-lg-4" data-featured-placeholder>
                                    <div class="card h-100 shadow-sm placeholder-glow">
                                        <div class="ratio ratio-4x3 bg-body-secondary rounded-top opacity-75"></div>
                                        <div class="card-body">
                                            <span class="placeholder col-9 mb-2"></span>
                                            <span class="placeholder col-7"></span>
                                        </div>
                                    </div>
                                </div>
                            <?php endfor; ?>
                        <?php endif; ?>
                    </div>
                    <div class="small text-muted mt-3">
                        <i class="bi bi-info-circle me-1"></i>
                        Preview mirrors the homepage grid layout. Fill at least three slots for best impact.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<template id="featuredAvailableTemplate">
    <li class="list-group-item d-flex align-items-start gap-3 flex-wrap" data-project-id>
        <div class="flex-grow-1" style="min-width: 0;">
            <div class="fw-semibold mb-1" data-project-title></div>
            <div class="text-muted small mb-0" data-project-description></div>
            <span class="badge bg-warning-subtle text-warning mt-2 d-none" data-project-status>Draft</span>
        </div>
        <div class="d-flex align-items-center ms-auto flex-shrink-0">
            <button type="button" class="btn btn-outline-primary btn-sm" data-featured-add>
                <i class="bi bi-plus-lg me-1"></i>
                Add
            </button>
        </div>
    </li>
</template>

<template id="featuredSelectedTemplate">
    <li class="list-group-item d-flex align-items-start gap-3 flex-wrap" data-featured-item data-sortable-item>
        <span class="sortable-handle text-muted" title="Drag to reorder" data-sortable-handle>
            <i class="bi bi-grip-vertical"></i>
        </span>
        <div class="flex-grow-1" style="min-width: 0;">
            <div class="fw-semibold mb-1" data-project-title></div>
            <div class="text-muted small mb-0" data-project-description></div>
        </div>
        <div class="d-flex align-items-center">
            <button type="button" class="btn btn-outline-danger btn-sm" data-featured-remove>
                <i class="bi bi-x-lg"></i>
                Remove
            </button>
        </div>
    </li>
</template>

<template id="featuredPreviewTemplate">
    <div class="col-sm-6 col-lg-4" data-featured-preview-item>
        <div class="card h-100 shadow-sm">
            <div class="ratio ratio-4x3">
                <img src="" alt="Project preview" class="rounded-top w-100 h-100" style="object-fit: cover;">
            </div>
            <div class="card-body">
                <h6 class="card-title mb-2" data-project-title></h6>
                <p class="card-text small text-muted mb-0" data-project-description></p>
            </div>
        </div>
    </div>
</template>

<template id="featuredPlaceholderTemplate">
    <div class="col-sm-6 col-lg-4" data-featured-placeholder>
        <div class="card h-100 shadow-sm placeholder-glow">
            <div class="ratio ratio-4x3 bg-body-secondary rounded-top opacity-75"></div>
            <div class="card-body">
                <span class="placeholder col-9 mb-2"></span>
                <span class="placeholder col-7"></span>
            </div>
        </div>
    </div>
</template>
