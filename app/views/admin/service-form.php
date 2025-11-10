<?php
$isEdit = isset($service);
$actionUrl = $isEdit
    ? url('admin/services/' . $service['id'] . '/edit')
    : url('admin/services/create');

$featuresValue = '';
if ($isEdit && !empty($service['features'])) {
    $decoded = json_decode($service['features'], true);
    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
        $featuresValue = implode("\n", $decoded);
    } else {
        $featuresValue = $service['features'];
    }
}
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h3 mb-0"><?= $isEdit ? 'Edit Service' : 'Create Service' ?></h1>
                <p class="text-muted mb-0"><?= $isEdit ? 'Update the selected service details.' : 'Add a new service offering to your website.' ?></p>
            </div>
            <a href="<?= url('admin/services') ?>" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-2"></i>
                Back to Services
            </a>
        </div>
    </div>

    <form method="POST" action="<?= $actionUrl ?>">
        <div class="row">
            <div class="col-lg-8">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Service Details</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="title" class="form-label">Service Title</label>
                            <input type="text"
                                   class="form-control"
                                   id="title"
                                   name="title"
                                   value="<?= htmlspecialchars($service['title'] ?? '') ?>"
                                   placeholder="UI/UX Design"
                                   required>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control"
                                      id="description"
                                      name="description"
                                      rows="4"
                                      placeholder="Describe what this service includes"
                                      required><?= htmlspecialchars($service['description'] ?? '') ?></textarea>
                            <div class="form-text">This appears as the main description on the services page.</div>
                        </div>

                        <div class="mb-3">
                            <label for="features" class="form-label">Key Features</label>
                            <textarea class="form-control"
                                      id="features"
                                      name="features"
                                      rows="5"
                                      placeholder="Each feature on a new line"><?= htmlspecialchars($featuresValue) ?></textarea>
                            <div class="form-text">Add one feature per line. They will be displayed as bullet points.</div>
                        </div>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Display Settings</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="icon" class="form-label">Bootstrap Icon</label>
                                <input type="text"
                                       class="form-control"
                                       id="icon"
                                       name="icon"
                                       value="<?= htmlspecialchars($service['icon'] ?? '') ?>"
                                       placeholder="bi-palette">
                                <div class="form-text">
                                    Use any Bootstrap icon class (e.g., <code>bi-globe</code>).
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="sort_order" class="form-label">Sort Order</label>
                                <input type="number"
                                       class="form-control"
                                       id="sort_order"
                                       name="sort_order"
                                       value="<?= htmlspecialchars($service['sort_order'] ?? 0) ?>"
                                       min="0">
                                <div class="form-text">Lower numbers appear first on the services list.</div>
                            </div>
                        </div>

                        <div class="form-check form-switch mt-3">
                            <input class="form-check-input"
                                   type="checkbox"
                                   role="switch"
                                   id="is_active"
                                   name="is_active"
                                   <?= !empty($service['is_active']) ? 'checked' : '' ?> >
                            <label class="form-check-label" for="is_active">Display this service on the website</label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Preview</h5>
                    </div>
                    <div class="card-body">
                        <div class="service-preview p-4 border rounded">
                            <div class="d-flex align-items-center mb-3">
                                <div class="preview-icon rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center me-3" style="width: 48px; height: 48px;">
                                    <i class="bi <?= htmlspecialchars($service['icon'] ?? 'bi-bounding-box') ?> fs-4"></i>
                                </div>
                                <div>
                                    <h5 class="mb-0" id="preview-title"><?= htmlspecialchars($service['title'] ?? 'Service Title') ?></h5>
                                    <small class="text-muted">Highlighted service preview</small>
                                </div>
                            </div>
                            <p class="text-muted" id="preview-description">
                                <?= htmlspecialchars($service['description'] ?? 'A short description of what this service includes and why it matters to clients.') ?>
                            </p>
                            <ul class="ps-3" id="preview-features">
                                <?php foreach (array_slice($featuresValue ? explode("\n", $featuresValue) : [], 0, 3) as $feature): ?>
                                    <?php if ($feature !== ''): ?>
                                        <li><?= htmlspecialchars($feature) ?></li>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                                <?php if (!$featuresValue): ?>
                                    <li>Feature one</li>
                                    <li>Feature two</li>
                                    <li>Feature three</li>
                                <?php endif; ?>
                            </ul>
                        </div>
                        <div class="mt-3">
                            <small class="text-muted">
                                <i class="bi bi-info-circle me-1"></i>
                                Preview updates dynamically as you edit the fields.
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-3">
            <div class="col-12">
                <div class="d-flex justify-content-end gap-2">
                    <a href="<?= url('admin/services') ?>" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg me-2"></i>
                        <?= $isEdit ? 'Save Changes' : 'Create Service' ?>
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const titleInput = document.getElementById('title');
    const descriptionInput = document.getElementById('description');
    const featuresInput = document.getElementById('features');

    const previewTitle = document.getElementById('preview-title');
    const previewDescription = document.getElementById('preview-description');
    const previewFeatures = document.getElementById('preview-features');

    if (titleInput) {
        titleInput.addEventListener('input', () => {
            previewTitle.textContent = titleInput.value || 'Service Title';
        });
    }

    if (descriptionInput) {
        descriptionInput.addEventListener('input', () => {
            previewDescription.textContent = descriptionInput.value || 'A short description of what this service includes and why it matters to clients.';
        });
    }

    if (featuresInput) {
        featuresInput.addEventListener('input', () => {
            const items = featuresInput.value.split('\n').map(item => item.trim()).filter(Boolean);
            previewFeatures.innerHTML = '';
            if (items.length === 0) {
                previewFeatures.innerHTML = '<li>Feature one</li><li>Feature two</li><li>Feature three</li>';
            } else {
                items.slice(0, 5).forEach(item => {
                    const li = document.createElement('li');
                    li.textContent = item;
                    previewFeatures.appendChild(li);
                });
                if (items.length > 5) {
                    const li = document.createElement('li');
                    li.textContent = `+${items.length - 5} more`; 
                    previewFeatures.appendChild(li);
                }
            }
        });
    }
});
</script>
