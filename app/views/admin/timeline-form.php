<?php
session_start_safe();

$formErrors = $_SESSION['flash']['form_errors'] ?? [];
$formData = $_SESSION['flash']['form_data'] ?? [];
unset($_SESSION['flash']['form_errors'], $_SESSION['flash']['form_data']);

$isEdit = isset($timeline_item);
$defaults = $timeline_defaults ?? [];
$statuses = $timeline_statuses ?? [];

$values = array_merge($defaults, $timeline_item ?? [], $formData);

$tagsInput = $values['tags'] ?? '';
if (is_array($tagsInput)) {
    $tagsInput = implode(', ', $tagsInput);
}
$tagsArray = array_values(array_filter(array_map('trim', explode(',', (string)$tagsInput)), static function ($value) {
    return $value !== '';
}));

$actionUrl = $isEdit
    ? url('admin/timeline/' . ($timeline_item['id'] ?? '') . '/edit')
    : url('admin/timeline/create');

$currentStatus = $values['status'] ?? 'published';
if (!array_key_exists($currentStatus, $statuses)) {
    $currentStatus = 'published';
}

$sortOrderValue = isset($values['sort_order']) && $values['sort_order'] !== null && $values['sort_order'] !== ''
    ? (int)$values['sort_order']
    : '';
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h1 class="h3 mb-0"><?= $isEdit ? 'Edit Timeline Entry' : 'Create Timeline Entry' ?></h1>
                <p class="text-muted mb-0">
                    <?= $isEdit ? 'Update the story, timeline, and tags for this milestone.' : 'Add a new milestone to showcase your experience or education.' ?>
                </p>
            </div>
            <a href="<?= url('admin/timeline') ?>" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-2"></i>
                Back to Timeline
            </a>
        </div>
    </div>

    <?php if (!empty($formErrors)): ?>
        <div class="alert alert-danger" role="alert">
            <h6 class="alert-heading">Please review the following:</h6>
            <ul class="mb-0 ps-3">
                <?php foreach ($formErrors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST" action="<?= $actionUrl ?>" class="row g-4 admin-form" data-timeline-form>
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Timeline Details</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="timeline_title" class="form-label">Title</label>
                        <input type="text"
                               class="form-control form-control-lg"
                               id="timeline_title"
                               name="title"
                               value="<?= htmlspecialchars($values['title'] ?? '') ?>"
                               placeholder="Lead Product Designer"
                               required
                               data-preview-target=".preview-timeline-item-title">
                        <div class="form-text">Role, credential, or milestone name.</div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="timeline_organization" class="form-label">Organization / Institution</label>
                            <input type="text"
                                   class="form-control"
                                   id="timeline_organization"
                                   name="organization"
                                   value="<?= htmlspecialchars($values['organization'] ?? '') ?>"
                                   placeholder="Aurum Labs"
                                   data-timeline-preview-meta>
                            <div class="form-text">Company, school, or group associated with this milestone.</div>
                        </div>
                        <div class="col-md-6">
                            <label for="timeline_date_range" class="form-label">Date Range</label>
                            <input type="text"
                                   class="form-control"
                                   id="timeline_date_range"
                                   name="date_range"
                    value="<?= htmlspecialchars($values['date_range'] ?? '') ?>"
                    placeholder="2019 - Present"
                                   data-timeline-preview-meta>
                <div class="form-text">Displayed alongside the organization. Example: 2021 - 2024.</div>
                        </div>
                    </div>

                    <div class="mt-3">
                        <label for="timeline_description" class="form-label">Summary</label>
                        <textarea class="form-control"
                                  id="timeline_description"
                                  name="description"
                                  rows="4"
                                  placeholder="Capture the impact, focus, or achievements from this period."
                                  data-preview-target=".preview-timeline-item-description"
                                  data-preview-property="html"><?= htmlspecialchars($values['description'] ?? '') ?></textarea>
                        <div class="form-text">Include outcomes, scope, or responsibilities. Keep it concise.</div>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Metadata</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-6">
                            <label for="timeline_tags" class="form-label">Tags</label>
                            <input type="text"
                                   class="form-control"
                                   id="timeline_tags"
                                   name="tags"
                                   value="<?= htmlspecialchars($tagsInput) ?>"
                                   placeholder="UX Strategy, Leadership, Design Systems"
                                   data-preview-list=".preview-timeline-item-tags"
                                   data-preview-list-type="pill"
                                   data-preview-list-limit="6">
                            <div class="form-text">Separate tags with commas. Highlight skills or focus areas (max 10).</div>
                        </div>
                        <div class="col-md-3">
                            <label for="timeline_sort_order" class="form-label">Sort Order</label>
                            <input type="number"
                                   class="form-control"
                                   id="timeline_sort_order"
                                   name="sort_order"
                                   min="0"
                                   value="<?= htmlspecialchars($sortOrderValue) ?>">
                            <div class="form-text">Lower numbers appear first.</div>
                        </div>
                        <div class="col-md-3">
                            <label for="timeline_status" class="form-label">Status</label>
                            <select class="form-select" id="timeline_status" name="status">
                                <?php foreach ($statuses as $key => $label): ?>
                                    <option value="<?= htmlspecialchars($key) ?>" <?= $currentStatus === $key ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($label) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-check form-switch mt-3">
                        <input class="form-check-input"
                               type="checkbox"
                               role="switch"
                               id="timeline_is_education"
                               name="is_education"
                               value="1"
                               <?= !empty($values['is_education']) ? 'checked' : '' ?>
                               data-timeline-type-toggle>
                        <label class="form-check-label" for="timeline_is_education">Mark as education milestone</label>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-eye me-2"></i>
                        Entry Preview
                    </h5>
                </div>
                <div class="card-body">
                    <div class="timeline-form-preview p-3 border rounded" data-timeline-form-preview>
                        <div class="timeline-preview-marker <?= !empty($values['is_education']) ? 'timeline-preview-marker-education' : '' ?>" data-timeline-type-marker></div>
                        <div class="timeline-form-preview-content">
                            <div class="d-flex justify-content-between align-items-start gap-2">
                                <div>
                                    <h5 class="mb-1 preview-timeline-item-title"><?= htmlspecialchars($values['title'] ?? 'Lead Product Designer') ?></h5>
                                    <div class="text-muted small preview-timeline-item-meta">
                                        <?php
                                        $metaParts = [];
                                        if (!empty($values['organization'])) {
                                            $metaParts[] = $values['organization'];
                                        }
                                        if (!empty($values['date_range'])) {
                                            $metaParts[] = $values['date_range'];
                                        }
                                        echo htmlspecialchars(implode(' | ', $metaParts));
                                        ?>
                                    </div>
                                </div>
                                <span class="badge rounded-pill bg-light text-muted" data-timeline-type-badge>
                                    <?= !empty($values['is_education']) ? 'Education' : 'Experience' ?>
                                </span>
                            </div>
                            <p class="small text-secondary mt-2 preview-timeline-item-description">
                                <?= nl2br(htmlspecialchars($values['description'] ?? 'Shape the product vision, mentor designers, and launch experiences that move key metrics.')) ?>
                            </p>
                            <div class="d-flex flex-wrap gap-2 preview-timeline-item-tags">
                                <?php if (!empty($tagsArray)): ?>
                                    <?php foreach (array_slice($tagsArray, 0, 4) as $tag): ?>
                                        <span class="badge bg-primary-subtle text-primary"><?= htmlspecialchars($tag) ?></span>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <span class="badge bg-primary-subtle text-primary">Design Leadership</span>
                                    <span class="badge bg-primary-subtle text-primary">Team Strategy</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="mt-3">
                        <small class="text-muted d-block">
                            <i class="bi bi-info-circle me-1"></i>
                            Preview mirrors the About page layout. Status controls visibility on the live site.
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="d-flex flex-wrap justify-content-end gap-2">
                <button type="button" class="btn btn-outline-light border" data-form-cancel>
                    <i class="bi bi-arrow-counterclockwise me-2"></i>
                    Reset Form
                </button>
                <a href="<?= url('admin/timeline') ?>" class="btn btn-outline-secondary">
                    <i class="bi bi-list-check me-2"></i>
                    Back to Timeline
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save me-2"></i>
                    <?= $isEdit ? 'Save Changes' : 'Create Timeline Entry' ?>
                </button>
            </div>
        </div>
    </form>
</div>
