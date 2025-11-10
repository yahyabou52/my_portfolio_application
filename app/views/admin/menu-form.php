<?php
$isEdit = isset($menu_item);
$actionUrl = $isEdit
    ? url('admin/navigation/' . $menu_item['id'] . '/edit')
    : url('admin/navigation/create');
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h3 mb-0"><?= $isEdit ? 'Edit Menu Item' : 'Create Menu Item' ?></h1>
                <p class="text-muted mb-0">Configure the navigation structure shown on your website</p>
            </div>
            <a href="<?= url('admin/navigation') ?>" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-2"></i>
                Back to Navigation
            </a>
        </div>
    </div>

    <form method="POST" action="<?= $actionUrl ?>">
        <div class="row">
            <div class="col-lg-8">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Menu Details</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="title" class="form-label">Menu Title</label>
                            <input type="text"
                                   class="form-control"
                                   id="title"
                                   name="title"
                                   value="<?= htmlspecialchars($menu_item['title'] ?? '') ?>"
                                   placeholder="Portfolio"
                                   required>
                        </div>

                        <div class="mb-3">
                            <label for="url" class="form-label">URL</label>
                            <input type="text"
                                   class="form-control"
                                   id="url"
                                   name="url"
                                   value="<?= htmlspecialchars($menu_item['url'] ?? '') ?>"
                                   placeholder="/portfolio"
                                   required>
                            <div class="form-text">Use relative URLs for internal pages (e.g., <code>/contact</code>).</div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="parent_id" class="form-label">Parent Item (optional)</label>
                                <select id="parent_id" name="parent_id" class="form-select" <?= $isEdit ? '' : '' ?>>
                                    <option value="">— Top Level —</option>
                                    <?php foreach ($parent_items ?? [] as $parent): ?>
                                        <?php if ($isEdit && $parent['id'] == ($menu_item['id'] ?? null)) continue; ?>
                                        <option value="<?= $parent['id'] ?>"
                                            <?= $isEdit && (int)($menu_item['parent_id'] ?? 0) === (int)$parent['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($parent['title']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="form-text">Nest this link under another menu item if desired.</div>
                            </div>
                            <div class="col-md-6">
                                <label for="target" class="form-label">Link Target</label>
                                <select id="target" name="target" class="form-select">
                                    <?php
                                    $targetValue = $menu_item['target'] ?? '_self';
                                    $targets = [
                                        '_self' => 'Same Tab',
                                        '_blank' => 'New Tab',
                                    ];
                                    foreach ($targets as $value => $label): ?>
                                        <option value="<?= $value ?>" <?= $targetValue === $value ? 'selected' : '' ?>>
                                            <?= $label ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Display Options</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="icon" class="form-label">Icon (optional)</label>
                            <input type="text"
                                   class="form-control"
                                   id="icon"
                                   name="icon"
                                   value="<?= htmlspecialchars($menu_item['icon'] ?? '') ?>"
                                   placeholder="bi-house">
                            <div class="form-text">Provide a Bootstrap icon class if you want an icon to appear beside the link.</div>
                        </div>

                        <div class="form-check form-switch">
                            <input class="form-check-input"
                                   type="checkbox"
                                   role="switch"
                                   id="is_active"
                                   name="is_active"
                                   <?= !empty($menu_item['is_active']) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="is_active">Display this menu item</label>
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
                        <div class="border rounded p-3">
                            <div class="d-flex align-items-center mb-2">
                                <i class="bi <?= htmlspecialchars($menu_item['icon'] ?? 'bi-link-45deg') ?> me-2"></i>
                                <strong id="preview-title"><?= htmlspecialchars($menu_item['title'] ?? 'Menu Title') ?></strong>
                            </div>
                            <p class="text-muted small mb-1">URL: <span id="preview-url"><?= htmlspecialchars($menu_item['url'] ?? '/example') ?></span></p>
                            <p class="text-muted small mb-0">Target: <span id="preview-target"><?= htmlspecialchars($menu_item['target'] ?? '_self') ?></span></p>
                        </div>
                        <div class="mt-3">
                            <small class="text-muted">
                                <i class="bi bi-info-circle me-1"></i>
                                Preview updates as you edit the fields.
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-3">
            <div class="col-12">
                <div class="d-flex justify-content-end gap-2">
                    <a href="<?= url('admin/navigation') ?>" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg me-2"></i>
                        <?= $isEdit ? 'Save Changes' : 'Create Menu Item' ?>
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const titleInput = document.getElementById('title');
    const urlInput = document.getElementById('url');
    const targetSelect = document.getElementById('target');

    const previewTitle = document.getElementById('preview-title');
    const previewUrl = document.getElementById('preview-url');
    const previewTarget = document.getElementById('preview-target');

    if (titleInput) {
        titleInput.addEventListener('input', () => {
            previewTitle.textContent = titleInput.value || 'Menu Title';
        });
    }

    if (urlInput) {
        urlInput.addEventListener('input', () => {
            previewUrl.textContent = urlInput.value || '/example';
        });
    }

    if (targetSelect) {
        targetSelect.addEventListener('change', () => {
            previewTarget.textContent = targetSelect.value;
        });
    }
});
</script>
