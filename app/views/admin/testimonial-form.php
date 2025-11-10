<?php
session_start_safe();

$formErrors = $_SESSION['flash']['form_errors'] ?? [];
$formData = $_SESSION['flash']['form_data'] ?? [];
unset($_SESSION['flash']['form_errors'], $_SESSION['flash']['form_data']);

$isEdit = isset($testimonial);
$actionUrl = $isEdit
    ? url('admin/testimonials/' . $testimonial['id'] . '/edit')
    : url('admin/testimonials/create');

$values = array_merge($testimonial ?? [], $formData);
$ratingValue = (int)($values['rating'] ?? 5);
$projectId = $values['project_id'] ?? null;
$isFeatured = !empty($values['is_featured']);
$isActive = array_key_exists('is_active', $values) ? (int)$values['is_active'] === 1 : true;
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h3 mb-0"><?= $isEdit ? 'Edit Testimonial' : 'Add Testimonial' ?></h1>
                <p class="text-muted mb-0">
                    <?= $isEdit ? 'Update client feedback and showcase details.' : 'Capture new testimonial details for your portfolio.' ?>
                </p>
            </div>
            <a href="<?= url('admin/testimonials') ?>" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-2"></i>
                Back to Testimonials
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
                    <h5 class="card-title mb-0">Client Details</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="client_name" class="form-label">Client Name</label>
                            <input type="text"
                                   class="form-control"
                                   id="client_name"
                                   name="client_name"
                                   value="<?= htmlspecialchars($values['client_name'] ?? '') ?>"
                                   placeholder="Sarah Johnson"
                                   required>
                        </div>
                        <div class="col-md-6">
                            <label for="client_company" class="form-label">Company <span class="text-muted">(optional)</span></label>
                            <input type="text"
                                   class="form-control"
                                   id="client_company"
                                   name="client_company"
                                   value="<?= htmlspecialchars($values['client_company'] ?? '') ?>"
                                   placeholder="TechCorp">
                        </div>
                    </div>

                    <div class="row g-3 mt-1">
                        <div class="col-md-6">
                            <label for="client_position" class="form-label">Position <span class="text-muted">(optional)</span></label>
                            <input type="text"
                                   class="form-control"
                                   id="client_position"
                                   name="client_position"
                                   value="<?= htmlspecialchars($values['client_position'] ?? '') ?>"
                                   placeholder="Product Manager">
                        </div>
                        <div class="col-md-6">
                            <label for="client_image" class="form-label">Image URL <span class="text-muted">(optional)</span></label>
                            <input type="text"
                                   class="form-control"
                                   id="client_image"
                                   name="client_image"
                                   value="<?= htmlspecialchars($values['client_image'] ?? '') ?>"
                                   placeholder="assets/images/testimonials/client.jpg">
                            <div class="form-text">Use a path relative to <code>public/</code>.</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Testimonial Content</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="testimonial_text" class="form-label">Feedback</label>
                        <textarea class="form-control"
                                  id="testimonial_text"
                                  name="testimonial_text"
                                  rows="6"
                                  placeholder="What did the client say?"
                                  required><?= htmlspecialchars($values['testimonial_text'] ?? '') ?></textarea>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="rating" class="form-label">Rating</label>
                            <select class="form-select" id="rating" name="rating" required>
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <option value="<?= $i ?>" <?= $ratingValue === $i ? 'selected' : '' ?>><?= $i ?> Star<?= $i > 1 ? 's' : '' ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="project_id" class="form-label">Linked Project <span class="text-muted">(optional)</span></label>
                            <select class="form-select" id="project_id" name="project_id">
                                <option value="">None</option>
                                <?php foreach (($project_options ?? []) as $item): ?>
                                    <option value="<?= (int)$item['id'] ?>" <?= ((int)$projectId === (int)$item['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($item['title']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="mt-3">
                        <label for="sort_order" class="form-label">Sort Order</label>
                        <input type="number"
                               class="form-control"
                               id="sort_order"
                               name="sort_order"
                               value="<?= htmlspecialchars($values['sort_order'] ?? 0) ?>"
                               min="0">
                        <div class="form-text">Lower numbers display first.</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Visibility</h5>
                </div>
                <div class="card-body">
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input"
                               type="checkbox"
                               role="switch"
                               id="is_featured"
                               name="is_featured"
                               <?= $isFeatured ? 'checked' : '' ?>>
                        <label class="form-check-label" for="is_featured">Feature this testimonial</label>
                    </div>

                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input"
                               type="checkbox"
                               role="switch"
                               id="is_active"
                               name="is_active"
                               <?= $isActive ? 'checked' : '' ?>>
                        <label class="form-check-label" for="is_active">Display on the website</label>
                    </div>

                    <div class="border rounded p-3 bg-light-subtle">
                        <h6 class="mb-2">Helpful Notes</h6>
                        <ul class="small mb-0 ps-3">
                            <li>Testimonials reinforce credibility and trust.</li>
                            <li>Featuring highlights key partnerships.</li>
                            <li>Keep feedback concise and authentic.</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a href="<?= url('admin/testimonials') ?>" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg me-2"></i>
                    <?= $isEdit ? 'Save Changes' : 'Create Testimonial' ?>
                </button>
            </div>
        </div>
    </form>
</div>
