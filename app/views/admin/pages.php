<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h3 mb-0">Pages</h1>
                <p class="text-muted mb-0">Edit static pages and SEO metadata</p>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Available Pages</h5>
        </div>
        <div class="card-body p-0">
            <?php if (!empty($pages)): ?>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th scope="col">Page</th>
                                <th scope="col">Meta Description</th>
                                <th scope="col" class="text-center">Status</th>
                                <th scope="col" class="text-center">Updated</th>
                                <th scope="col" class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pages as $pageItem): ?>
                                <tr>
                                    <td>
                                        <h6 class="mb-0 text-capitalize"><?= htmlspecialchars(str_replace('-', ' ', $pageItem['page_key'])) ?></h6>
                                        <small class="text-muted">Key: <?= htmlspecialchars($pageItem['page_key']) ?></small>
                                    </td>
                                    <td>
                                        <p class="text-muted mb-0">
                                            <?= htmlspecialchars(str_limit($pageItem['meta_description'] ?? '—', 120, '…')) ?>
                                        </p>
                                    </td>
                                    <td class="text-center">
                                        <?php if (!empty($pageItem['is_active'])): ?>
                                            <span class="badge bg-success-subtle text-success">
                                                <i class="bi bi-eye me-1"></i>Visible
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary-subtle text-secondary">
                                                <i class="bi bi-eye-slash me-1"></i>Hidden
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <small class="text-muted">
                                            <?= !empty($pageItem['updated_at']) ? date('M j, Y', strtotime($pageItem['updated_at'])) : '—' ?>
                                        </small>
                                    </td>
                                    <td class="text-end">
                                        <a href="<?= url('admin/pages/' . $pageItem['page_key'] . '/edit') ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-pencil-square"></i>
                                            Edit
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="p-4 text-center text-muted">
                    <i class="bi bi-journal-x fs-1 d-block mb-2"></i>
                    <p class="mb-0">No pages found. Pages are created automatically from the database seeding.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
