<div class="container-fluid">
    <div class="row align-items-center mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h3 mb-0">Services</h1>
                <p class="text-muted mb-0">Manage the services that appear on your website</p>
            </div>
            <a href="<?= url('admin/services/create') ?>" class="btn btn-primary">
                <i class="bi bi-plus-lg me-2"></i>
                Add New Service
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">All Services</h5>
        </div>
        <div class="card-body p-0">
            <?php if (!empty($services)): ?>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th scope="col">Title</th>
                                <th scope="col" class="text-center">Icon</th>
                                <th scope="col">Features</th>
                                <th scope="col" class="text-center">Status</th>
                                <th scope="col" class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($services as $service): ?>
                                <?php
                                $features = [];
                                if (!empty($service['features'])) {
                                    $decoded = json_decode($service['features'], true);
                                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                                        $features = $decoded;
                                    }
                                }
                                ?>
                                <tr>
                                    <td>
                                        <h6 class="mb-1"><?= htmlspecialchars($service['title']) ?></h6>
                                        <p class="text-muted small mb-0">
                                            <?= htmlspecialchars(str_limit($service['description'], 120, '…')) ?>
                                        </p>
                                    </td>
                                    <td class="text-center">
                                        <?php if (!empty($service['icon'])): ?>
                                            <span class="badge bg-primary-subtle text-primary">
                                                <i class="bi <?= htmlspecialchars($service['icon']) ?>"></i>
                                                <?= htmlspecialchars($service['icon']) ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($features)): ?>
                                            <div class="d-flex flex-wrap gap-2">
                                                <?php foreach (array_slice($features, 0, 3) as $feature): ?>
                                                    <span class="badge bg-secondary-subtle text-secondary"><?= htmlspecialchars($feature) ?></span>
                                                <?php endforeach; ?>
                                                <?php if (count($features) > 3): ?>
                                                    <span class="badge bg-secondary">+<?= count($features) - 3 ?></span>
                                                <?php endif; ?>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-muted small">No features listed</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if (!empty($service['is_active'])): ?>
                                            <span class="badge bg-success-subtle text-success">
                                                <i class="bi bi-check2-circle me-1"></i>Active
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary-subtle text-secondary">
                                                <i class="bi bi-pause-circle me-1"></i>Inactive
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <div class="btn-group" role="group">
                                            <a href="<?= url('admin/services/' . $service['id'] . '/edit') ?>" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>
                                            <form method="POST" action="<?= url('admin/services/' . $service['id'] . '/delete') ?>" class="d-inline" onsubmit="return confirm('Delete this service?');">
                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="p-4 text-center text-muted">
                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                    <p class="mb-0">No services found. Click "Add New Service" to create one.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
