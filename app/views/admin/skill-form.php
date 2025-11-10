<?php
session_start_safe();

$formErrors = $_SESSION['flash']['form_errors'] ?? [];
$formData = $_SESSION['flash']['form_data'] ?? [];
unset($_SESSION['flash']['form_errors'], $_SESSION['flash']['form_data']);

$isEdit = isset($skill);
$actionUrl = $isEdit
    ? url('admin/skills/' . $skill['id'] . '/edit')
    : url('admin/skills/create');

$values = array_merge($skill ?? [], $formData);
$proficiency = (int)($values['proficiency_level'] ?? 80);
$isActive = array_key_exists('is_active', $values) ? (int)$values['is_active'] === 1 : true;
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h3 mb-0"><?= $isEdit ? 'Edit Skill' : 'Add Skill' ?></h1>
                <p class="text-muted mb-0">
                    <?= $isEdit ? 'Update skill proficiency and details.' : 'Describe a capability you want to highlight.' ?>
                </p>
            </div>
            <a href="<?= url('admin/skills') ?>" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-2"></i>
                Back to Skills
            </a>
        </div>
    </div>

    <?php if (!empty($formErrors)): ?>
        <div class="alert alert-danger">
            <h6 class="alert-heading">Please review the following:</h6>
            <ul class="mb-0 ps-3">
                <?php foreach ($formErrors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST" action="<?= $actionUrl ?>" class="row g-4">
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Skill Details</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="name" class="form-label">Skill Name</label>
                        <input type="text"
                               class="form-control"
                               id="name"
                               name="name"
                               value="<?= htmlspecialchars($values['name'] ?? '') ?>"
                               placeholder="UI/UX Design"
                               required>
                    </div>

                    <div class="mb-3">
                        <label for="category" class="form-label">Category <span class="text-muted">(optional)</span></label>
                        <input type="text"
                               class="form-control"
                               id="category"
                               name="category"
                               value="<?= htmlspecialchars($values['category'] ?? '') ?>"
                               placeholder="Design">
                    </div>

                    <div class="mb-3">
                        <label for="icon" class="form-label">Icon Class <span class="text-muted">(optional)</span></label>
                        <input type="text"
                               class="form-control"
                               id="icon"
                               name="icon"
                               value="<?= htmlspecialchars($values['icon'] ?? '') ?>"
                               placeholder="bi-palette">
                        <div class="form-text">Use a Bootstrap icon class to display beside the skill.</div>
                    </div>

                    <div class="mb-0">
                        <label for="description" class="form-label">Description <span class="text-muted">(optional)</span></label>
                        <textarea class="form-control"
                                  id="description"
                                  name="description"
                                  rows="5"
                                  placeholder="Explain how you apply this skill"><?= htmlspecialchars($values['description'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Display Settings</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="proficiency_level" class="form-label d-flex justify-content-between">
                            <span>Proficiency</span>
                            <span id="proficiency-output" class="fw-semibold"><?= $proficiency ?>%</span>
                        </label>
                        <input type="range"
                               class="form-range"
                               id="proficiency_level"
                               name="proficiency_level"
                               min="0"
                               max="100"
                               step="1"
                               value="<?= $proficiency ?>"
                               oninput="document.getElementById('proficiency-output').textContent = this.value + '%';">
                    </div>

                    <div class="mb-3">
                        <label for="sort_order" class="form-label">Sort Order</label>
                        <input type="number"
                               class="form-control"
                               id="sort_order"
                               name="sort_order"
                               value="<?= htmlspecialchars($values['sort_order'] ?? 0) ?>"
                               min="0">
                        <div class="form-text">Controls the list order in the skills section.</div>
                    </div>

                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input"
                               type="checkbox"
                               role="switch"
                               id="is_active"
                               name="is_active"
                               <?= $isActive ? 'checked' : '' ?>>
                        <label class="form-check-label" for="is_active">Display this skill</label>
                    </div>

                    <div class="border rounded p-3 bg-light-subtle">
                        <h6 class="mb-2">Quick Reminder</h6>
                        <ul class="small mb-0 ps-3">
                            <li>Keep proficiency honest for credibility.</li>
                            <li>Group similar skills with categories.</li>
                            <li>Icons add visual flair in the public view.</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a href="<?= url('admin/skills') ?>" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg me-2"></i>
                    <?= $isEdit ? 'Save Changes' : 'Create Skill' ?>
                </button>
            </div>
        </div>
    </form>
</div>
