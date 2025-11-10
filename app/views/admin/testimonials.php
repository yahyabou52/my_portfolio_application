<?php
$initialTestimonialsJson = $testimonials_json ?? '[]';
$placeholderImage = $testimonial_placeholder ?? asset('images/testimonials/default-avatar.jpg');
$testimonialRoutes = $testimonial_routes ?? [];

$createUrl = $testimonialRoutes['create'] ?? url('admin/testimonials');
$updateBase = $testimonialRoutes['update_base'] ?? url('admin/testimonials');
$deleteBase = $testimonialRoutes['delete_base'] ?? url('admin/testimonials');
$reorderUrl = $testimonialRoutes['reorder'] ?? url('admin/testimonials/order');

$totalTestimonials = isset($testimonials) && is_countable($testimonials) ? count($testimonials) : 0;
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
                <div>
                    <h1 class="h3 mb-0">Testimonials</h1>
                    <p class="text-muted mb-0">Curate homepage testimonials, control visibility, and preview the slider live.</p>
                </div>
                <button type="button" class="btn btn-primary" data-testimonials-add>
                    <i class="bi bi-plus-lg me-2"></i>
                    Add Testimonial
                </button>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-xxl-7">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <h5 class="card-title mb-0">
                            <i class="bi bi-chat-quote me-2"></i>
                            Manage Testimonials
                        </h5>
                        <small class="text-muted">Drag to reorder the slider. Toggle visibility to hide testimonials without deleting them.</small>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-secondary btn-sm" data-testimonials-cancel disabled>Cancel</button>
                        <button type="button" class="btn btn-primary btn-sm" data-testimonials-save disabled>Save Changes</button>
                    </div>
                </div>
                <div class="card-body"
                     data-testimonials-manager
                     data-testimonials-initial="<?= $initialTestimonialsJson ?>"
                     data-testimonials-default-image="<?= htmlspecialchars($placeholderImage, ENT_QUOTES, 'UTF-8') ?>"
                     data-testimonials-create-url="<?= htmlspecialchars($createUrl, ENT_QUOTES, 'UTF-8') ?>"
                     data-testimonials-update-base="<?= htmlspecialchars($updateBase, ENT_QUOTES, 'UTF-8') ?>"
                     data-testimonials-delete-base="<?= htmlspecialchars($deleteBase, ENT_QUOTES, 'UTF-8') ?>"
                     data-testimonials-reorder-url="<?= htmlspecialchars($reorderUrl, ENT_QUOTES, 'UTF-8') ?>">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                        <div class="small text-muted">
                            <i class="bi bi-lightning-charge me-1"></i>
                            Changes update the preview instantly; remember to save to publish.
                        </div>
                        <span class="badge bg-primary-subtle text-primary" data-testimonials-count><?= $totalTestimonials ?> total</span>
                    </div>

                    <div class="small mb-3 text-muted d-none" data-testimonials-feedback></div>

                    <ul class="list-group"
                        data-testimonials-list
                        data-sortable-list>
                    </ul>

                    <div class="border rounded text-center py-4 text-muted<?= $totalTestimonials > 0 ? ' d-none' : '' ?>" data-testimonials-empty>
                        <i class="bi bi-chat-left-quote fs-4 d-block mb-2"></i>
                        <p class="mb-1">No testimonials yet.</p>
                        <small>Add your first story to build trust right away.</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xxl-5">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title mb-0">
                            <i class="bi bi-eye me-2"></i>
                            Live Preview
                        </h5>
                        <small class="text-muted">Shows the active slider order and visibility.</small>
                    </div>
                    <span class="badge bg-success-subtle text-success d-none" data-testimonials-draft-indicator>Draft Preview</span>
                </div>
                <div class="card-body">
                    <div class="testimonials-preview" data-testimonials-preview></div>
                    <div class="border rounded text-center py-4 text-muted<?= $totalTestimonials > 0 ? ' d-none' : '' ?>" data-testimonials-preview-empty>
                        <i class="bi bi-layout-text-window-reverse fs-4 d-block mb-2"></i>
                        <p class="mb-1">No visible testimonials yet.</p>
                        <small>Visible testimonials will appear here with the newest order.</small>
                    </div>
                    <div class="mt-3 small text-muted">
                        <i class="bi bi-info-circle me-1"></i>
                        Preview mirrors homepage styling; final typography follows the active theme.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<template id="testimonialListItemTemplate">
    <li class="list-group-item d-flex align-items-start gap-3 flex-wrap"
        data-testimonial-item
        data-sortable-item>
        <span class="sortable-handle text-muted" data-sortable-handle title="Drag to reorder">
            <i class="bi bi-grip-vertical"></i>
        </span>
        <div class="d-flex align-items-start flex-grow-1 gap-3">
            <div class="flex-shrink-0">
                <img src="" alt="Client avatar" class="rounded-circle object-fit-cover" width="56" height="56" data-testimonial-thumb>
            </div>
            <div class="flex-grow-1 min-w-0">
                <div class="d-flex justify-content-between align-items-start gap-3">
                    <div class="min-w-0">
                        <div class="fw-semibold text-truncate" data-testimonial-name></div>
                        <div class="text-muted small text-truncate" data-testimonial-meta></div>
                    </div>
                    <div class="text-end" data-testimonial-rating></div>
                </div>
                <p class="small text-muted mb-2 mt-2" data-testimonial-snippet></p>
                <div class="d-flex flex-wrap align-items-center gap-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" data-testimonial-visible>
                        <label class="form-check-label small">Visible</label>
                    </div>
                    <span class="badge bg-warning-subtle text-warning d-none" data-testimonial-dirty>Unsaved</span>
                    <div class="ms-auto d-flex gap-2">
                        <button type="button" class="btn btn-sm btn-outline-primary" data-testimonial-edit>
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-danger" data-testimonial-delete>
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </li>
</template>

<template id="testimonialPreviewTemplate">
    <div class="testimonial-preview-card border rounded p-3 mb-3 position-relative" data-testimonial-preview-item>
        <div class="d-flex gap-3 align-items-start">
            <img src="" alt="Client avatar" class="rounded-circle flex-shrink-0 object-fit-cover" width="64" height="64" data-preview-thumb>
            <div class="flex-grow-1">
                <div class="d-flex align-items-center gap-1 mb-2" data-preview-stars></div>
                <p class="mb-2" data-preview-text></p>
                <div class="fw-semibold" data-preview-name></div>
                <div class="text-muted small" data-preview-meta></div>
            </div>
        </div>
        <span class="badge bg-info-subtle text-info position-absolute top-0 end-0 m-2 d-none" data-preview-draft>Draft</span>
    </div>
</template>

<div class="modal fade" id="testimonialModal" tabindex="-1" aria-labelledby="testimonialModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <form class="modal-content admin-form" data-testimonial-form data-auto-loading="false" enctype="multipart/form-data">
            <input type="hidden" name="testimonial_id" value="" data-testimonial-field="id">
            <input type="hidden" name="existing_image_path" value="" data-testimonial-field="existing-image">

            <div class="modal-header">
                <h5 class="modal-title" id="testimonialModalLabel">Add Testimonial</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger d-none" data-testimonial-form-errors></div>
                <div class="row g-4">
                    <div class="col-lg-7">
                        <div class="row g-3">
                            <div class="col-12">
                                <label for="testimonial_client_name" class="form-label">Client Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="testimonial_client_name" name="client_name" placeholder="Alex Morgan" required data-preview-target="#testimonialModalPreviewName">
                            </div>
                            <div class="col-md-6">
                                <label for="testimonial_client_position" class="form-label">Position</label>
                                <input type="text" class="form-control" id="testimonial_client_position" name="client_position" placeholder="Product Director">
                            </div>
                            <div class="col-md-6">
                                <label for="testimonial_client_company" class="form-label">Company</label>
                                <input type="text" class="form-control" id="testimonial_client_company" name="client_company" placeholder="Acme Corp">
                            </div>
                            <div class="col-12">
                                <label for="testimonial_text" class="form-label">Testimonial <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="testimonial_text" name="testimonial_text" rows="5" placeholder="Share the client's feedback..." required data-preview-target="#testimonialModalPreviewText"></textarea>
                                <div class="form-text">Minimum 12 characters. Keep it concise and authentic.</div>
                            </div>
                        </div>

                        <div class="row g-3 mt-1">
                            <div class="col-md-6">
                                <label for="testimonial_rating" class="form-label">Rating</label>
                                <select class="form-select" id="testimonial_rating" name="rating" required>
                                    <?php for ($i = 5; $i >= 1; $i--): ?>
                                        <option value="<?= $i ?>"><?= $i ?> Star<?= $i > 1 ? 's' : '' ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Visibility</label>
                                <div class="form-check form-switch pt-2">
                                    <input class="form-check-input" type="checkbox" id="testimonial_display_toggle" name="display_toggle" value="1" checked>
                                    <label class="form-check-label" for="testimonial_display_toggle">Show on homepage</label>
                                </div>
                            </div>
                        </div>

                        <div class="row g-3 mt-1">
                            <div class="col-md-7">
                                <label for="testimonial_client_image" class="form-label">Client Image</label>
                                <input type="file" class="form-control" id="testimonial_client_image" name="client_image_file" accept="image/*" data-preview-file="#testimonialModalPreviewImage">
                                <div class="form-text">Uploads are stored in <code>assets/uploads/</code>.</div>
                            </div>
                            <div class="col-md-5 d-flex align-items-end">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="1" id="testimonial_remove_image" name="remove_client_image">
                                    <label class="form-check-label" for="testimonial_remove_image">Remove current image</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-5">
                        <div class="card border shadow-sm">
                            <div class="card-header">
                                <h6 class="mb-0">Modal Preview</h6>
                            </div>
                            <div class="card-body">
                                <div class="d-flex gap-3">
                                    <img src="<?= htmlspecialchars($placeholderImage, ENT_QUOTES, 'UTF-8') ?>" alt="Preview avatar" class="rounded-circle object-fit-cover" width="64" height="64" id="testimonialModalPreviewImage">
                                    <div>
                                        <div class="text-warning mb-2" id="testimonialModalPreviewStars"></div>
                                        <p class="mb-2" id="testimonialModalPreviewText">Client feedback will appear here.</p>
                                        <div class="fw-semibold" id="testimonialModalPreviewName">Client Name</div>
                                        <div class="text-muted small" id="testimonialModalPreviewMeta">Position · Company</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary" data-testimonial-submit>
                    <i class="bi bi-check-lg me-2"></i>
                    Save Testimonial
                </button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="testimonialDeleteModal" tabindex="-1" aria-labelledby="testimonialDeleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="testimonialDeleteModalLabel">Delete Testimonial</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-1">Are you sure you want to delete the testimonial from <strong data-testimonial-delete-name>this client</strong>?</p>
                <p class="text-muted small">This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" data-testimonial-delete-confirm>
                    <i class="bi bi-trash me-2"></i>
                    Delete
                </button>
            </div>
        </div>
    </div>
</div>