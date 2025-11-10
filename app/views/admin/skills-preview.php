<?php
$categories = $skill_categories ?? [];
$initialPayload = htmlspecialchars(json_encode($categories, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), ENT_QUOTES, 'UTF-8');
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

<div class="container-fluid" data-skills-manager data-skills-initial="<?= $initialPayload ?>">
    <div class="row align-items-center mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h1 class="h3 mb-0">Skills &amp; Tools Preview</h1>
                <p class="text-muted mb-0">Curate the homepage skills grid with categories, drag-and-drop ordering, and live preview.</p>
            </div>
            <div class="text-end">
                <button type="button" class="btn btn-outline-primary" data-add-category>
                    <i class="bi bi-plus-lg me-2"></i>
                    Add Category
                </button>
            </div>
        </div>
    </div>

    <?php if (!empty($formErrors)): ?>
        <div class="alert alert-danger" role="alert">
            <h6 class="alert-heading mb-2">We found a few issues while saving:</h6>
            <ul class="mb-0">
                <?php foreach ($formErrors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="row g-4">
        <div class="col-xl-8">
            <form method="POST" data-skills-form class="h-100 d-flex flex-column">
                <div class="card flex-grow-1">
                    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-3">
                        <div>
                            <h5 class="card-title mb-1">Homepage Skills Structure</h5>
                            <small class="text-muted">Reorder categories, toggle visibility, and fine-tune individual skills.</small>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-outline-secondary" data-skills-cancel>Cancel</button>
                            <button type="submit" class="btn btn-primary" data-skills-save>
                                <i class="bi bi-save me-2"></i>
                                Save Changes
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <ul class="list-group gap-3" data-category-list data-sortable-list></ul>
                        <div class="text-center text-muted py-5 d-none" data-category-empty>
                            <i class="bi bi-box-seam fs-3 d-block mb-2"></i>
                            <p class="mb-1">No categories added yet.</p>
                            <p class="small mb-0">Use the <strong>Add Category</strong> button to start building your skills grid.</p>
                        </div>
                        <div class="small text-muted mt-3" data-skills-feedback></div>
                    </div>
                    <div class="card-footer">
                        <input type="hidden" name="skills_structure" data-skills-payload value="<?= $initialPayload ?>">
                    </div>
                </div>
            </form>
        </div>
        <div class="col-xl-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0 d-flex align-items-center gap-2">
                        <i class="bi bi-columns-gap"></i>
                        Homepage Preview
                    </h5>
                </div>
                <div class="card-body">
                    <div class="skills-preview-grid" data-skills-preview></div>
                    <div class="text-center text-muted py-5" data-preview-empty>
                        <i class="bi bi-layout-sidebar-inset fs-3 d-block mb-2"></i>
                        <p class="mb-1">Preview updates as you add categories and skills.</p>
                        <p class="small mb-0">Dimmed cards indicate hidden content.</p>
                    </div>
                    <div class="small text-muted mt-3">
                        <i class="bi bi-info-circle me-2"></i>
                        Preview mirrors the homepage Skills &amp; Tools section. Hidden categories and skills appear dimmed to match live behaviour.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Category Modal -->
<div class="modal fade" id="categoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form class="modal-content" data-category-form>
            <div class="modal-header">
                <h5 class="modal-title" data-category-modal-title>Add Category</h5>
                <button type="button" class="btn-close" data-category-modal-cancel aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger d-none" data-category-modal-error></div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Category Title</label>
                    <input type="text" class="form-control" data-category-field="title" maxlength="150" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Icon Class <span class="text-muted fw-normal">(optional)</span></label>
                    <input type="text" class="form-control" data-category-field="icon" maxlength="120">
                    <div class="form-text">Example: <code>bi-lightning-charge</code> or <code>fas fa-bolt</code>.</div>
                </div>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" role="switch" data-category-field="visible" checked>
                    <label class="form-check-label">Display this category on the website</label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-category-modal-cancel>Cancel</button>
                <button type="submit" class="btn btn-primary">Save Category</button>
            </div>
        </form>
    </div>
</div>

<!-- Skill Modal -->
<div class="modal fade" id="skillModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form class="modal-content" data-skill-form>
            <div class="modal-header">
                <h5 class="modal-title" data-skill-modal-title>Add Skill</h5>
                <button type="button" class="btn-close" data-skill-modal-cancel aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger d-none" data-skill-modal-error></div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Skill Name</label>
                    <input type="text" class="form-control" data-skill-field="name" maxlength="150" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Proficiency (%)</label>
                    <input type="number" class="form-control" data-skill-field="proficiency" min="0" max="100" step="1" required>
                    <div class="form-text">Must be between 0 and 100.</div>
                </div>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" role="switch" data-skill-field="visible" checked>
                    <label class="form-check-label">Display this skill in the category</label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-skill-modal-cancel>Cancel</button>
                <button type="submit" class="btn btn-primary">Save Skill</button>
            </div>
        </form>
    </div>
</div>

<!-- Templates -->
<template id="skillCategoryTemplate">
    <li class="list-group-item skills-manager-category" data-category-item data-sortable-item>
        <div class="d-flex align-items-start gap-3">
            <span class="sortable-handle text-muted" data-sortable-handle title="Drag to reorder">
                <i class="bi bi-grip-vertical"></i>
            </span>
            <div class="flex-grow-1">
                <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                    <div>
                        <h6 class="mb-1" data-category-title>Category</h6>
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <span class="badge bg-secondary-subtle text-secondary d-none" data-category-hidden-badge>Hidden</span>
                            <code class="text-muted small d-none" data-category-icon></code>
                        </div>
                    </div>
                    <div class="btn-group btn-group-sm" role="group">
                        <button type="button" class="btn btn-outline-primary" data-category-edit title="Edit category">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button type="button" class="btn btn-outline-danger" data-category-delete title="Delete category">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
            <div class="form-check form-switch form-switch-sm mt-1">
                <input class="form-check-input" type="checkbox" role="switch" data-category-visible-toggle>
                <label class="form-check-label small">Visible</label>
            </div>
        </div>
        <div class="mt-3">
            <ul class="list-group gap-2" data-skill-list data-sortable-list></ul>
            <button type="button" class="btn btn-outline-primary btn-sm mt-3" data-skill-add>
                <i class="bi bi-plus-lg me-1"></i>
                Add Skill
            </button>
        </div>
    </li>
</template>

<template id="skillItemTemplate">
    <li class="list-group-item skills-manager-skill d-flex align-items-center gap-3" data-skill-item data-sortable-item>
        <span class="sortable-handle text-muted" data-sortable-handle title="Drag to reorder">
            <i class="bi bi-grip-vertical"></i>
        </span>
        <div class="flex-grow-1">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="fw-semibold" data-skill-name>Skill Name</span>
                    <span class="badge bg-secondary-subtle text-secondary ms-2 d-none" data-skill-hidden-badge>Hidden</span>
                </div>
                <span class="text-muted" data-skill-percent>0%</span>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <div class="form-check form-switch form-switch-sm">
                <input class="form-check-input" type="checkbox" role="switch" data-skill-visible-toggle>
            </div>
            <button type="button" class="btn btn-outline-primary btn-sm" data-skill-edit title="Edit skill">
                <i class="bi bi-pencil"></i>
            </button>
            <button type="button" class="btn btn-outline-danger btn-sm" data-skill-delete title="Delete skill">
                <i class="bi bi-trash"></i>
            </button>
        </div>
    </li>
</template>

<template id="skillPreviewCategoryTemplate">
    <div class="skills-preview-card" data-preview-category>
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex align-items-start gap-3 mb-3">
                    <div class="skills-preview-icon" data-preview-icon-wrapper>
                        <i class="bi bi-palette" data-preview-icon></i>
                    </div>
                    <div>
                        <h6 class="mb-1" data-preview-title>Category</h6>
                        <span class="badge bg-secondary-subtle text-secondary d-none" data-preview-hidden-badge>Hidden</span>
                    </div>
                </div>
                <div class="skills-preview-list" data-preview-skill-list></div>
            </div>
        </div>
    </div>
</template>

<template id="skillPreviewSkillTemplate">
    <div class="skill-meter" data-preview-skill>
        <div class="skill-meter-row">
            <span class="skill-meter-name" data-preview-skill-name>Skill</span>
            <span class="skill-meter-percent" data-preview-skill-percent>0%</span>
        </div>
        <div class="skill-meter-track">
            <div class="skill-meter-fill" data-preview-skill-bar></div>
        </div>
    </div>
</template>
