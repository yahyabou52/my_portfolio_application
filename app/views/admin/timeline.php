<?php
$timelineItems = $timeline_items ?? [];
$timelineCounts = $timeline_counts ?? ['total' => 0, 'published' => 0, 'draft' => 0];
$timelineGroupCounts = $timeline_group_counts ?? ['experience' => 0, 'education' => 0];
$timelineOrderValue = $timeline_order_value ?? '';
$timelineStatuses = $timeline_statuses ?? [];
$timelineItemsJson = $timeline_items_json ?? '[]';
$timelineReorderUrl = $timeline_reorder_url ?? url('admin/timeline/reorder');
$previewGroups = $timeline_preview_groups ?? [];

$timelineItemsJsonEscaped = htmlspecialchars($timelineItemsJson, ENT_QUOTES, 'UTF-8');
$timelineOrderValueEscaped = htmlspecialchars($timelineOrderValue, ENT_QUOTES, 'UTF-8');
$timelineReorderUrlEscaped = htmlspecialchars($timelineReorderUrl, ENT_QUOTES, 'UTF-8');

$hasTimelineItems = !empty($timelineItems);
$hasPreviewGroups = false;
foreach ($previewGroups as $groupItems) {
    if (!empty($groupItems)) {
        $hasPreviewGroups = true;
        break;
    }
}
?>

<div class="container-fluid">
    <div class="row align-items-center mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h1 class="h3 mb-0">Experience Timeline</h1>
                <p class="text-muted mb-0">Curate the work and education milestones that appear on your About page.</p>
            </div>
            <div class="d-flex align-items-center gap-3">
                <div class="d-flex gap-2 flex-wrap">
                    <span class="badge bg-primary-subtle text-primary">
                        <i class="bi bi-card-list me-1"></i>
                        <?= (int)($timelineCounts['total'] ?? 0) ?> total
                    </span>
                    <span class="badge bg-success-subtle text-success">
                        <i class="bi bi-check-circle me-1"></i>
                        <?= (int)($timelineCounts['published'] ?? 0) ?> live
                    </span>
                    <span class="badge bg-secondary-subtle text-secondary">
                        <i class="bi bi-pencil me-1"></i>
                        <?= (int)($timelineCounts['draft'] ?? 0) ?> drafts
                    </span>
                </div>
                <a href="<?= url('admin/timeline/create') ?>" class="btn btn-primary">
                    <i class="bi bi-plus-lg me-2"></i>
                    Add Timeline Entry
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-8">
            <div
                class="card mb-4"
                data-timeline-manager
                data-timeline-items="<?= $timelineItemsJsonEscaped ?>"
                data-timeline-initial-order="<?= $timelineOrderValueEscaped ?>"
                data-timeline-reorder-url="<?= $timelineReorderUrlEscaped ?>"
                data-timeline-count-total="<?= (int)($timelineCounts['total'] ?? 0) ?>"
                data-timeline-count-published="<?= (int)($timelineCounts['published'] ?? 0) ?>"
                data-timeline-count-draft="<?= (int)($timelineCounts['draft'] ?? 0) ?>"
                data-timeline-count-experience="<?= (int)($timelineGroupCounts['experience'] ?? 0) ?>"
                data-timeline-count-education="<?= (int)($timelineGroupCounts['education'] ?? 0) ?>"
                data-timeline-preview-target="#timelinePreviewRoot"
            >
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div>
                        <h5 class="card-title mb-0">
                            <i class="bi bi-clock-history me-2"></i>
                            Timeline Entries
                        </h5>
                        <small class="text-muted">Drag to reorder. Published entries appear on the About page.</small>
                    </div>
                    <?php if ($hasTimelineItems): ?>
                        <span class="badge bg-light text-muted">
                            <i class="bi bi-arrows-move me-1"></i>
                            Drag rows to update sort order
                        </span>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <?php if ($hasTimelineItems): ?>
                        <div class="d-flex justify-content-between flex-wrap align-items-center gap-2 mb-3">
                            <div class="d-flex flex-wrap align-items-center gap-2">
                                <div class="btn-group btn-group-sm" role="group" aria-label="Filter by status">
                                    <button type="button" class="btn btn-outline-secondary active" data-timeline-filter="status" data-filter-value="all">All statuses</button>
                                    <button type="button" class="btn btn-outline-secondary" data-timeline-filter="status" data-filter-value="published">Published</button>
                                    <button type="button" class="btn btn-outline-secondary" data-timeline-filter="status" data-filter-value="draft">Drafts</button>
                                </div>
                                <div class="btn-group btn-group-sm" role="group" aria-label="Filter by type">
                                    <button type="button" class="btn btn-outline-secondary active" data-timeline-filter="type" data-filter-value="all">All types</button>
                                    <button type="button" class="btn btn-outline-secondary" data-timeline-filter="type" data-filter-value="experience">Experience</button>
                                    <button type="button" class="btn btn-outline-secondary" data-timeline-filter="type" data-filter-value="education">Education</button>
                                </div>
                                <button type="button" class="btn btn-link btn-sm px-2" data-timeline-filter-reset>
                                    Clear filters
                                </button>
                            </div>
                            <small class="text-muted" data-timeline-visible-count>
                                Showing <?= count($timelineItems) ?> of <?= count($timelineItems) ?> entries
                            </small>
                        </div>

                        <div class="alert alert-info d-none small py-2 mb-3" role="status" data-timeline-feedback></div>

                        <ul
                            class="list-group timeline-admin-list"
                            data-sortable-list
                            data-sortable-order-input="#timelineOrderInput"
                            data-sortable-save-button="#timelineOrderSubmit"
                            data-timeline-list
                        >
                            <?php foreach ($timelineItems as $item): ?>
                                <?php
                                $itemId = (int)($item['id'] ?? 0);
                                $organization = trim((string)($item['organization'] ?? ''));
                                $dateRange = trim((string)($item['date_range'] ?? ''));
                                $description = trim((string)($item['description'] ?? ''));
                                $tags = $item['tags'] ?? [];
                                $statusKey = $item['status'] ?? 'draft';
                                $statusLabel = $timelineStatuses[$statusKey] ?? ucfirst($statusKey);
                                $typeKey = !empty($item['is_education']) ? 'education' : 'experience';
                                $statusBadgeClass = $statusKey === 'published'
                                    ? 'badge bg-success-subtle text-success'
                                    : 'badge bg-secondary-subtle text-secondary';
                                $typeBadgeClass = $typeKey === 'education'
                                    ? 'badge bg-info-subtle text-info'
                                    : 'badge bg-primary-subtle text-primary';
                                $typeLabel = $typeKey === 'education' ? 'Education' : 'Experience';

                                $metaParts = [];
                                if ($organization !== '') {
                                    $metaParts[] = $organization;
                                }
                                if ($dateRange !== '') {
                                    $metaParts[] = $dateRange;
                                }
                                $metaText = implode(' | ', $metaParts);
                                ?>
                                <li
                                    class="list-group-item timeline-admin-item"
                                    data-sortable-item
                                    data-sortable-id="<?= $itemId ?>"
                                    data-timeline-item-id="<?= $itemId ?>"
                                    data-timeline-status="<?= htmlspecialchars($statusKey) ?>"
                                    data-timeline-type="<?= $typeKey ?>"
                                >
                                    <div class="d-flex align-items-start gap-3 flex-wrap">
                                        <span class="sortable-handle text-muted" title="Drag to reorder" data-sortable-handle>
                                            <i class="bi bi-grip-vertical"></i>
                                        </span>
                                        <div class="flex-grow-1 timeline-admin-item-body">
                                            <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                                                <div>
                                                    <h6 class="mb-1 timeline-admin-item-title"><?= htmlspecialchars($item['title'] ?? '') ?></h6>
                                                    <?php if ($metaText !== ''): ?>
                                                        <div class="text-muted small"><?= htmlspecialchars($metaText) ?></div>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="d-flex align-items-center gap-2">
                                                    <span class="<?= $typeBadgeClass ?>"><?= $typeLabel ?></span>
                                                    <span class="<?= $statusBadgeClass ?>"><?= htmlspecialchars($statusLabel) ?></span>
                                                </div>
                                            </div>
                                            <?php if ($description !== ''): ?>
                                                <p class="text-muted small mt-2 mb-2">
                                                    <?php
                                                    $descriptionSuffix = mb_strlen($description) > 180 ? '...' : '';
                                                    echo htmlspecialchars(str_limit($description, 180, $descriptionSuffix));
                                                    ?>
                                                </p>
                                            <?php endif; ?>
                                            <?php if (!empty($tags)): ?>
                                                <div class="d-flex flex-wrap gap-2 mb-1">
                                                    <?php foreach (array_slice($tags, 0, 6) as $tag): ?>
                                                        <span class="badge rounded-pill bg-light text-secondary"><?= htmlspecialchars($tag) ?></span>
                                                    <?php endforeach; ?>
                                                    <?php if (count($tags) > 6): ?>
                                                        <span class="badge bg-light text-muted">+<?= count($tags) - 6 ?></span>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="ms-auto">
                                            <div class="btn-group" role="group">
                                                <a href="<?= url('admin/timeline/' . $itemId . '/edit') ?>" class="btn btn-sm btn-outline-primary" title="Edit entry">
                                                    <i class="bi bi-pencil-square"></i>
                                                </a>
                                                <form method="POST" action="<?= url('admin/timeline/' . $itemId . '/delete') ?>" class="d-inline" onsubmit="return confirm('Delete this timeline entry?');">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete entry">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>

                        <div class="text-center py-4 text-muted border rounded d-none" data-timeline-empty>
                            <i class="bi bi-funnel fs-3 d-block mb-2"></i>
                            <p class="mb-1">No timeline entries match the selected filters.</p>
                            <p class="mb-0 small">Clear filters to see the full list.</p>
                        </div>

                        <form method="POST" action="<?= $timelineReorderUrlEscaped ?>" class="d-flex flex-wrap justify-content-end align-items-center mt-3 gap-2" data-timeline-order-form>
                            <input type="hidden" name="order" id="timelineOrderInput" value="<?= $timelineOrderValueEscaped ?>">
                            <div class="flex-grow-1 text-muted small" data-timeline-order-feedback></div>
                            <button type="button" class="btn btn-outline-light border btn-sm" data-timeline-reset-order>
                                <i class="bi bi-arrow-counterclockwise me-1"></i>
                                Reset Order
                            </button>
                            <button type="submit" class="btn btn-outline-secondary btn-sm" id="timelineOrderSubmit" disabled>
                                <i class="bi bi-arrow-repeat me-1"></i>
                                Save Order
                            </button>
                        </form>
                    <?php else: ?>
                        <div class="text-center py-5 text-muted border rounded">
                            <i class="bi bi-calendar-range fs-1 mb-3 d-block"></i>
                            <p class="mb-1">No timeline entries yet.</p>
                            <p class="mb-0 small">Start by adding roles, milestones, or education to bring your story to life.</p>
                        </div>
                    <?php endif; ?>
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
                <div class="card-body" id="timelinePreviewRoot" data-timeline-preview-root>
                    <div data-timeline-preview-groups>
                        <?php if ($hasPreviewGroups): ?>
                            <?php foreach (['experience' => 'Experience', 'education' => 'Education'] as $groupKey => $groupLabel): ?>
                                <?php $groupItems = $previewGroups[$groupKey] ?? []; ?>
                                <?php if (empty($groupItems)) { continue; } ?>
                                <section class="timeline-preview-group mb-4" data-timeline-preview-group="<?= $groupKey ?>">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <h6 class="mb-0"><?= $groupLabel ?></h6>
                                        <span class="badge bg-light text-muted"><?= count($groupItems) ?> <?= count($groupItems) === 1 ? 'entry' : 'entries' ?></span>
                                    </div>
                                    <div class="timeline-preview-stack">
                                        <?php foreach ($groupItems as $index => $preview): ?>
                                            <?php
                                            $previewTags = $preview['tags'] ?? [];
                                            $previewOrg = trim((string)($preview['organization'] ?? ''));
                                            $previewDates = trim((string)($preview['date_range'] ?? ''));
                                            $previewMetaParts = [];
                                            if ($previewOrg !== '') {
                                                $previewMetaParts[] = $previewOrg;
                                            }
                                            if ($previewDates !== '') {
                                                $previewMetaParts[] = $previewDates;
                                            }
                                            $previewMeta = implode(' | ', $previewMetaParts);
                                            ?>
                                            <div class="timeline-preview-item<?= $index > 0 ? ' mt-3' : '' ?>">
                                                <div class="timeline-preview-marker <?= !empty($preview['is_education']) ? 'timeline-preview-marker-education' : '' ?>"></div>
                                                <div class="timeline-preview-content">
                                                    <div class="d-flex justify-content-between align-items-start gap-2">
                                                        <div>
                                                            <h6 class="mb-1"><?= htmlspecialchars($preview['title'] ?? '') ?></h6>
                                                            <?php if ($previewMeta !== ''): ?>
                                                                <div class="text-muted small"><?= htmlspecialchars($previewMeta) ?></div>
                                                            <?php endif; ?>
                                                        </div>
                                                        <span class="badge rounded-pill bg-light text-muted">
                                                            <?= !empty($preview['is_education']) ? 'Education' : 'Experience' ?>
                                                        </span>
                                                    </div>
                                                    <?php if (!empty($preview['description'])): ?>
                                                        <p class="small text-secondary mt-2 mb-2">
                                                            <?php
                                                            $previewSuffix = mb_strlen($preview['description']) > 140 ? '...' : '';
                                                            echo htmlspecialchars(str_limit($preview['description'], 140, $previewSuffix));
                                                            ?>
                                                        </p>
                                                    <?php endif; ?>
                                                    <?php if (!empty($previewTags)): ?>
                                                        <div class="d-flex flex-wrap gap-2">
                                                            <?php foreach (array_slice($previewTags, 0, 4) as $tag): ?>
                                                                <span class="badge bg-primary-subtle text-primary"><?= htmlspecialchars($tag) ?></span>
                                                            <?php endforeach; ?>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </section>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    <div class="text-center py-4 text-muted<?= $hasPreviewGroups ? ' d-none' : '' ?>" data-timeline-preview-empty>
                        <i class="bi bi-info-circle fs-3 d-block mb-2"></i>
                        <p class="mb-0">Published entries will appear here as they would on the About page.</p>
                    </div>
                    <div class="mt-3">
                        <small class="text-muted d-block">
                            <i class="bi bi-lightning-charge me-1"></i>
                            Publish entries to display them on the public About page timeline.
                        </small>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-lightbulb me-2"></i>
                        Timeline Tips
                    </h5>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled small mb-0">
                        <li class="mb-2"><strong>Lead with impact:</strong> Share the outcomes you drove, not just responsibilities.</li>
                        <li class="mb-2"><strong>Keep it crisp:</strong> Aim for 2-3 sentences per entry for easy scanning.</li>
                        <li class="mb-2"><strong>Tag wisely:</strong> Use tags to highlight the skills or focus areas of each milestone.</li>
                        <li class="mb-0"><strong>Order matters:</strong> The list displays chronologically based on this ordering.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<template id="timelinePreviewGroupTemplate">
    <section class="timeline-preview-group mb-4">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h6 class="mb-0" data-preview-group-title></h6>
            <span class="badge bg-light text-muted" data-preview-group-count></span>
        </div>
        <div class="timeline-preview-stack" data-preview-group-stack></div>
    </section>
</template>

<template id="timelinePreviewItemTemplate">
    <div class="timeline-preview-item">
        <div class="timeline-preview-marker" data-preview-item-marker></div>
        <div class="timeline-preview-content">
            <div class="d-flex justify-content-between align-items-start gap-2">
                <div>
                    <h6 class="mb-1" data-preview-item-title></h6>
                    <div class="text-muted small" data-preview-item-meta></div>
                </div>
                <span class="badge rounded-pill bg-light text-muted" data-preview-item-badge></span>
            </div>
            <p class="small text-secondary mt-2 mb-2 d-none" data-preview-item-description></p>
            <div class="d-flex flex-wrap gap-2" data-preview-item-tags></div>
        </div>
    </div>
</template>
