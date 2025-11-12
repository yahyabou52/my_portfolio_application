<?php
$faqsJson = $faqs_json ?? '[]';
$routesJson = $routes_json ?? '{}';
$stats = $stats ?? ['total' => 0, 'visible' => 0, 'hidden' => 0];
?>

<div class="container-fluid" data-faq-manager data-faq-initial="<?= $faqsJson ?>" data-faq-routes="<?= $routesJson ?>">
    <div class="row align-items-center mb-4">
        <div class="col-lg-7">
            <h1 class="h3 mb-1">Frequently Asked Questions</h1>
            <p class="text-muted mb-0">Curate the accordion that appears on the Services page.</p>
        </div>
        <div class="col-lg-5 d-flex justify-content-lg-end gap-2 mt-3 mt-lg-0">
            <button type="button" class="btn btn-outline-secondary" data-faq-refresh>
                <i class="bi bi-arrow-clockwise me-2"></i>
                Refresh
            </button>
            <button type="button" class="btn btn-primary" data-faq-add>
                <i class="bi bi-plus-lg me-2"></i>
                Add FAQ
            </button>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card h-100 shadow-sm">
                <div class="card-body text-center py-3">
                    <p class="text-muted text-uppercase small mb-1">Total</p>
                    <p class="display-6 mb-0" data-faq-stat="total"><?= (int)$stats['total'] ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100 shadow-sm">
                <div class="card-body text-center py-3">
                    <p class="text-muted text-uppercase small mb-1">Visible</p>
                    <p class="display-6 mb-0 text-success" data-faq-stat="visible"><?= (int)$stats['visible'] ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100 shadow-sm">
                <div class="card-body text-center py-3">
                    <p class="text-muted text-uppercase small mb-1">Hidden</p>
                    <p class="display-6 mb-0 text-muted" data-faq-stat="hidden"><?= (int)$stats['hidden'] ?></p>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-xl-8">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <h5 class="card-title mb-1">FAQ Ordering &amp; Visibility</h5>
                        <small class="text-muted">Drag to reorder, toggle visibility, and edit content inline.</small>
                    </div>
                </div>
                <div class="card-body">
                    <ul class="list-group gap-3" data-faq-list data-sortable-list></ul>
                    <div class="text-center text-muted py-5 d-none" data-faq-empty>
                        <i class="bi bi-question-square fs-3 d-block mb-2"></i>
                        <p class="mb-1">No FAQs yet.</p>
                        <p class="small mb-0">Add questions to help visitors quickly find answers.</p>
                    </div>
                    <div class="small text-muted mt-3" data-faq-feedback></div>
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-1 d-flex align-items-center gap-2">
                        <i class="bi bi-layout-text-sidebar"></i>
                        Live Preview
                    </h5>
                    <small class="text-muted">Matches the Services page accordion.</small>
                </div>
                <div class="card-body">
                    <div class="faq-preview" data-faq-preview></div>
                    <div class="text-center text-muted py-5" data-faq-preview-empty>
                        <i class="bi bi-question-circle fs-3 d-block mb-2"></i>
                        <p class="mb-1">Preview updates as you edit FAQs.</p>
                        <p class="small mb-0">Hidden items stay off the Services page.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- FAQ Modal -->
<div class="modal fade" id="faqModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <form class="modal-content" data-faq-form>
            <div class="modal-header">
                <h5 class="modal-title" data-faq-modal-title>Add FAQ</h5>
                <button type="button" class="btn-close" data-faq-modal-cancel aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger d-none" data-faq-modal-error></div>
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label fw-semibold">Question</label>
                        <input type="text" class="form-control" data-faq-field="question" maxlength="255" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Answer</label>
                        <textarea class="form-control" rows="6" data-faq-field="answer" maxlength="5000" required></textarea>
                    </div>
                    <div class="col-12">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" data-faq-field="visible" checked>
                            <label class="form-check-label">Visible on Services page</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-faq-modal-cancel>Cancel</button>
                <button type="submit" class="btn btn-primary" data-faq-modal-submit>Save FAQ</button>
            </div>
        </form>
    </div>
</div>

<!-- Templates -->
<template id="faqListItemTemplate">
    <li class="list-group-item d-flex align-items-start gap-3" data-faq-item data-sortable-item>
        <span class="sortable-handle text-muted" data-sortable-handle title="Drag to reorder">
            <i class="bi bi-grip-vertical"></i>
        </span>
        <div class="flex-grow-1">
            <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <h6 class="mb-0" data-faq-question>Question</h6>
                        <span class="badge bg-secondary d-none" data-faq-hidden-badge>Hidden</span>
                    </div>
                    <p class="text-muted small mb-0" data-faq-answer-preview>Answer excerpt</p>
                </div>
            </div>
        </div>
        <div class="d-flex flex-column align-items-end gap-2">
            <div class="btn-group btn-group-sm" role="group">
                <button type="button" class="btn btn-outline-primary" data-faq-edit title="Edit">
                    <i class="bi bi-pencil"></i>
                </button>
                <button type="button" class="btn btn-outline-danger" data-faq-delete title="Delete">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
            <div class="form-check form-switch form-switch-sm">
                <input class="form-check-input" type="checkbox" role="switch" data-faq-visible-toggle>
            </div>
        </div>
    </li>
</template>

<template id="faqPreviewTemplate">
    <div class="accordion-item" data-faq-preview-item>
        <h2 class="accordion-header">
            <button class="accordion-button" type="button" data-faq-preview-question>Sample Question</button>
        </h2>
        <div class="accordion-collapse collapse show">
            <div class="accordion-body" data-faq-preview-answer>Sample answer content.</div>
        </div>
    </div>
</template>
