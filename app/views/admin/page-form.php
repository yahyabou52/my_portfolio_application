<?php if (empty($page_data)): ?>
    <div class="alert alert-danger m-4">
        <strong>Page data unavailable.</strong> Please return to the pages list.
    </div>
    <?php return; ?>
<?php endif; ?>

<?php
$formAction = url('admin/pages/' . $page_data['page_key'] . '/edit');
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h3 mb-0">Edit "<?= htmlspecialchars(ucwords(str_replace('-', ' ', $page_data['page_key']))) ?>" Page</h1>
                <p class="text-muted mb-0">Update content and SEO settings for this page</p>
            </div>
            <a href="<?= url('admin/pages') ?>" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-2"></i>
                Back to Pages
            </a>
        </div>
    </div>

    <form method="POST" action="<?= $formAction ?>">
        <div class="row">
            <div class="col-lg-8">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Page Content</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="title" class="form-label">Page Title</label>
                            <input type="text"
                                   class="form-control"
                                   id="title"
                                   name="title"
                                   value="<?= htmlspecialchars($page_data['title'] ?? '') ?>"
                                   required>
                        </div>

                        <div class="mb-3">
                            <label for="meta_description" class="form-label">Meta Description</label>
                            <textarea class="form-control"
                                      id="meta_description"
                                      name="meta_description"
                                      rows="3"
                                      maxlength="300"
                                      placeholder="Brief summary for search engines (max 300 characters)"><?= htmlspecialchars($page_data['meta_description'] ?? '') ?></textarea>
                            <div class="form-text">This appears in search results and social previews.</div>
                        </div>

                        <div class="mb-3">
                            <label for="content" class="form-label">Main Content</label>
                            <textarea class="form-control"
                                      id="content"
                                      name="content"
                                      rows="12"
                                      placeholder="Write the main body of the page here."><?= htmlspecialchars($page_data['content'] ?? '') ?></textarea>
                            <div class="form-text">You can use HTML to add formatting, lists, and links.</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Publish Settings</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Page Key</label>
                            <input type="text"
                                   class="form-control"
                                   value="<?= htmlspecialchars($page_data['page_key']) ?>"
                                   disabled>
                            <div class="form-text">Unique identifier used in templates.</div>
                        </div>

                        <div class="form-check form-switch">
                            <input class="form-check-input"
                                   type="checkbox"
                                   role="switch"
                                   id="is_active"
                                   name="is_active"
                                   <?= !empty($page_data['is_active']) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="is_active">Page is visible on the website</label>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Quick Reference</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled mb-0">
                            <li class="mb-2"><strong>Created:</strong> <?= !empty($page_data['created_at']) ? date('M j, Y', strtotime($page_data['created_at'])) : '—' ?></li>
                            <li class="mb-2"><strong>Last Updated:</strong> <?= !empty($page_data['updated_at']) ? date('M j, Y', strtotime($page_data['updated_at'])) : '—' ?></li>
                            <li class="mb-0"><strong>URL:</strong> <code><?= htmlspecialchars('/' . trim($page_data['page_key'], '/')) ?></code></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-3">
            <div class="col-12 d-flex justify-content-end gap-2">
                <a href="<?= url('admin/pages') ?>" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg me-2"></i>
                    Save Page
                </button>
            </div>
        </div>
    </form>
</div>
