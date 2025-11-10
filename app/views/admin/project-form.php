<?php
session_start_safe();

$formErrors = $_SESSION['flash']['form_errors'] ?? [];
$formData = $_SESSION['flash']['form_data'] ?? [];
unset($_SESSION['flash']['form_errors'], $_SESSION['flash']['form_data']);

$isEdit = isset($project);
$actionUrl = $isEdit
    ? url('admin/projects/' . $project['id'] . '/edit')
    : url('admin/projects/create');

$values = array_merge($project ?? [], $formData);

$technologiesValue = $values['technologies'] ?? '';
if (is_array($technologiesValue)) {
    $technologiesValue = implode(', ', $technologiesValue);
} elseif (is_string($technologiesValue)) {
    $decodedTechnologies = json_decode($technologiesValue, true);
    if (json_last_error() === JSON_ERROR_NONE && is_array($decodedTechnologies)) {
        $technologiesValue = implode(', ', $decodedTechnologies);
    }
}

$galleryValue = $values['gallery'] ?? '';
if (is_array($galleryValue)) {
    $galleryValue = implode("\n", $galleryValue);
} elseif (is_string($galleryValue)) {
    $decodedGallery = json_decode($galleryValue, true);
    if (json_last_error() === JSON_ERROR_NONE && is_array($decodedGallery)) {
        $galleryValue = implode("\n", $decodedGallery);
    }
}

$currentStatus = $values['status'] ?? 'draft';
$featuredChecked = !empty($values['featured']) || !empty($values['is_featured']);
$featuredImage = !empty($values['image_url']) ? media_url($values['image_url']) : asset('images/projects/default.jpg');
$clientVisibility = $values['client_visibility'] ?? 'yes';
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h3 mb-0"><?= $isEdit ? 'Edit Project' : 'Create Project' ?></h1>
                <p class="text-muted mb-0">
                    <?= $isEdit ? 'Update project details, descriptions, and visibility.' : 'Add a new project to your portfolio.' ?>
                </p>
            </div>
            <a href="<?= url('admin/projects') ?>" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-2"></i>
                Back to Projects
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

    <form method="POST" action="<?= $actionUrl ?>" class="row g-4 admin-form" enctype="multipart/form-data">
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Project Basics</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="title" class="form-label">Project Title</label>
                        <input type="text"
                               class="form-control form-control-lg"
                               id="title"
                               name="title"
                               value="<?= htmlspecialchars($values['title'] ?? '') ?>"
                               placeholder="Healthcare Dashboard"
                               required
                               data-preview-target=".preview-project-title">
                    </div>

                    <div class="mb-3">
                        <label for="slug" class="form-label">Slug <span class="text-muted">(optional)</span></label>
                        <input type="text"
                               class="form-control form-control-lg"
                               id="slug"
                               name="slug"
                               value="<?= htmlspecialchars($values['slug'] ?? '') ?>"
                               placeholder="healthcare-dashboard">
                        <div class="form-text">Used in the project URL. Leave blank to generate automatically.</div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="category" class="form-label">Category</label>
                            <input type="text"
                                   class="form-control form-control-lg"
                                   id="category"
                                   name="category"
                                   value="<?= htmlspecialchars($values['category'] ?? '') ?>"
                                   placeholder="Web Design"
                                   required
                                   data-preview-target=".preview-project-category">
                        </div>
                        <div class="col-md-6">
                            <label for="client" class="form-label">Client <span class="text-muted">(optional)</span></label>
                            <input type="text"
                                   class="form-control form-control-lg"
                                   id="client"
                                   name="client"
                                   value="<?= htmlspecialchars($values['client'] ?? '') ?>"
                                   placeholder="MedTech Systems"
                                   data-preview-target=".preview-project-client-text"
                                   data-preview-toggle-class="d-none"
                                   data-preview-toggle-selector=".preview-project-client">
                        </div>
                    </div>

                    <div class="row g-3 mt-1">
                        <div class="col-md-6">
                            <label for="project_url" class="form-label">Project URL <span class="text-muted">(optional)</span></label>
                            <input type="url"
                                   class="form-control form-control-lg"
                                   id="project_url"
                                   name="project_url"
                                   value="<?= htmlspecialchars($values['project_url'] ?? '') ?>"
                                   placeholder="https://example.com/project">
                        </div>
                        <div class="col-md-6">
                            <label for="github_url" class="form-label">GitHub URL <span class="text-muted">(optional)</span></label>
                            <input type="url"
                                   class="form-control form-control-lg"
                                   id="github_url"
                                   name="github_url"
                                   value="<?= htmlspecialchars($values['github_url'] ?? '') ?>"
                                   placeholder="https://github.com/username/project">
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Descriptions</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="short_description" class="form-label">Summary</label>
                        <textarea class="form-control form-control-lg"
                                  id="short_description"
                                  name="short_description"
                                  rows="3"
                                  placeholder="Short overview shown in listings"
                                  required
                                  data-preview-target=".preview-project-summary"
                                  data-preview-property="text"><?= htmlspecialchars($values['short_description'] ?? '') ?></textarea>
                    </div>
                    <div class="mb-0">
                        <label for="description" class="form-label">Detailed Description</label>
                        <textarea class="form-control form-control-lg"
                                  id="description"
                                  name="description"
                                  rows="8"
                                  placeholder="Full project case study"
                                  required
                                  data-preview-target=".preview-project-description"
                                  data-preview-property="html"><?= htmlspecialchars($values['description'] ?? '') ?></textarea>
                        <div class="form-text">Use paragraphs to explain goals, process, and outcomes.</div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Media & Tags</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="image_url" class="form-label">Featured Image</label>
                        <div class="row g-2 align-items-center">
                            <div class="col-md-8">
                                <input type="text"
                                       class="form-control form-control-lg"
                                       id="image_url"
                                       name="image_url"
                                       value="<?= htmlspecialchars($values['image_url'] ?? '') ?>"
                                       placeholder="assets/uploads/project-cover.jpg"
                                       data-preview-image=".preview-project-image">
                                <div class="form-text">Use an existing path or leave blank when uploading.</div>
                            </div>
                            <div class="col-md-4">
                                <input type="file"
                                       class="form-control form-control-lg"
                                       id="image_file"
                                       name="image_file"
                                       accept="image/*"
                                       data-preview-file=".preview-project-image">
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="technologies" class="form-label">Technologies</label>
                        <input type="text"
                               class="form-control form-control-lg"
                               id="technologies"
                               name="technologies"
                               value="<?= htmlspecialchars($technologiesValue) ?>"
                               placeholder="Figma, React, Node.js"
                               data-preview-list=".preview-project-tech"
                               data-preview-list-type="pill"
                               data-preview-list-limit="6">
                        <div class="form-text">Separate items with commas.</div>
                    </div>

                    <div class="mb-0">
                        <label for="gallery" class="form-label">Gallery Images <span class="text-muted">(optional)</span></label>
                        <textarea class="form-control form-control-lg"
                                  id="gallery"
                                  name="gallery"
                                  rows="4"
                                  placeholder="Enter one image path per line"><?= htmlspecialchars($galleryValue) ?></textarea>
                        <div class="form-text">Paths are relative to <code>assets/images/projects/gallery/</code>.</div>
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
                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select form-select-lg" id="status" name="status">
                            <option value="published" <?= $currentStatus === 'published' ? 'selected' : '' ?>>Published</option>
                            <option value="draft" <?= $currentStatus === 'draft' ? 'selected' : '' ?>>Draft</option>
                        </select>
                    </div>

                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input"
                               type="checkbox"
                               role="switch"
                               id="featured"
                               name="featured"
                               <?= $featuredChecked ? 'checked' : '' ?>>
                        <label class="form-check-label" for="featured">Mark as featured project</label>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Client Visibility</label>
                        <div class="btn-group" role="group" aria-label="Client visibility toggle">
                            <input type="radio"
                                   class="btn-check"
                                   name="client_visibility"
                                   id="clientVisibilityYes"
                                   value="yes"
                                   data-preview-target="#clientVisibilityStatus"
                                   data-preview-value="visible"
                                   data-preview-html="<?= htmlspecialchars('<span class=\'badge text-bg-success text-uppercase small fw-semibold\'>Visible on live site</span>', ENT_QUOTES, 'UTF-8') ?>"
                                   <?= $clientVisibility === 'yes' ? 'checked' : '' ?>>
                            <label class="btn btn-outline-primary" for="clientVisibilityYes">Show client</label>

                            <input type="radio"
                                   class="btn-check"
                                   name="client_visibility"
                                   id="clientVisibilityNo"
                                   value="no"
                                   data-preview-target="#clientVisibilityStatus"
                                   data-preview-value="hidden"
                                   data-preview-html="<?= htmlspecialchars('<span class=\'badge text-bg-secondary text-uppercase small fw-semibold\'>Hidden from live site</span>', ENT_QUOTES, 'UTF-8') ?>"
                                   <?= $clientVisibility === 'no' ? 'checked' : '' ?>>
                            <label class="btn btn-outline-primary" for="clientVisibilityNo">Hide client</label>
                        </div>
                        <div id="clientVisibilityStatus" class="mt-2 small text-muted">
                            <?= $clientVisibility === 'no'
                                ? '<span class="badge text-bg-secondary text-uppercase small fw-semibold">Hidden from live site</span>'
                                : '<span class="badge text-bg-success text-uppercase small fw-semibold">Visible on live site</span>' ?>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="sort_order" class="form-label">Sort Order</label>
                        <input type="number"
                               class="form-control form-control-lg"
                               id="sort_order"
                               name="sort_order"
                               value="<?= htmlspecialchars($values['sort_order'] ?? 0) ?>"
                               min="0">
                        <div class="form-text">Lower numbers appear first.</div>
                    </div>

                    <div class="border rounded p-3 bg-light-subtle">
                        <h6 class="mb-2">Quick Tips</h6>
                        <ul class="small mb-0 ps-3">
                            <li>Use descriptive slugs for SEO.</li>
                            <li>Ensure image paths exist in <code>public/assets</code>.</li>
                            <li>Draft projects stay hidden on the public site.</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a href="<?= url('admin/projects') ?>" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg me-2"></i>
                    <?= $isEdit ? 'Save Changes' : 'Create Project' ?>
                </button>
            </div>
        </div>

        <div class="col-lg-12">
            <div class="card project-live-preview mt-2">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-eye me-2"></i>
                        Live Preview
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-4 align-items-center">
                        <div class="col-md-5">
                            <div class="project-preview-image-wrapper rounded shadow-sm overflow-hidden">
                                <img src="<?= htmlspecialchars($featuredImage) ?>"
                                     alt="Project image preview"
                                     class="img-fluid preview-project-image">
                            </div>
                        </div>
                        <div class="col-md-7">
                            <div class="d-flex flex-wrap gap-2 mb-3 preview-project-tech"></div>
                            <h4 class="preview-project-title mb-2"><?= htmlspecialchars($values['title'] ?? 'Project Title') ?></h4>
                            <p class="text-muted mb-1 preview-project-category">
                                <?= htmlspecialchars($values['category'] ?? 'Category') ?>
                            </p>
                            <p id="projectClientPreview"
                               class="small text-muted mb-2 preview-project-client <?= empty($values['client']) || $clientVisibility === 'no' ? 'd-none' : '' ?>"
                               data-preview-toggle
                               data-toggle-selector="input[name='client_visibility']"
                               data-preview-formatter="html"
                               data-preview-target="#projectClientPreview">
                                <i class="bi bi-building me-1"></i>
                                <span class="preview-project-client-text"><?= htmlspecialchars($values['client'] ?? '') ?></span>
                            </p>
                            <p class="preview-project-summary fw-semibold mb-3">
                                <?= htmlspecialchars($values['short_description'] ?? 'Short overview shown in listings') ?>
                            </p>
                            <div class="preview-project-description text-muted small">
                                <?= nl2br(htmlspecialchars($values['description'] ?? 'Full project description will appear here.')) ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
