<?php $totalProjects = isset($projects) && is_countable($projects) ? count($projects) : 0; ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0 text-gray-800">Projects Management</h1>
                    <p class="mb-0 text-muted">Manage your portfolio projects</p>
                </div>
                <a href="<?= url('admin/projects/create') ?>" class="btn btn-primary">
                    <i class="bi bi-plus-lg me-2"></i>
                    Add Project
                </a>
            </div>
        </div>
    </div>

    <!-- Projects Table -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 fw-semibold text-primary">All Projects</h6>
                    <span class="text-muted small">Total: <?= $totalProjects ?></span>
                </div>
                <div class="card-body">
                    <?php if (empty($projects)): ?>
                        <div class="text-center py-4">
                            <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No projects found</h5>
                            <p class="text-muted">Start by creating your first project</p>
                            <a href="<?= url('admin/projects/create') ?>" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Create Project
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Title</th>
                                        <th>Category</th>
                                        <th>Client</th>
                                        <th class="text-center">Status</th>
                                        <th class="text-center">Featured</th>
                                        <th class="text-center">Sort</th>
                                        <th>Created</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($projects as $project): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <?php if (!empty($project['main_image_path'])): ?>
                                                        <img src="<?= htmlspecialchars(media_url($project['main_image_path'])) ?>" alt="<?= htmlspecialchars($project['title'] ?? 'Project') ?>" class="rounded me-2" style="width: 40px; height: 40px; object-fit: cover;">
                                                    <?php endif; ?>
                                                    <div>
                                                        <strong><?= htmlspecialchars($project['title']) ?></strong>
                                                        <?php if (!empty($project['short_description'])): ?>
                                                            <br><small class="text-muted"><?= htmlspecialchars(substr($project['short_description'], 0, 50)) ?>...</small>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </td>
                                            <td><?= htmlspecialchars($project['category'] ?? 'Uncategorized') ?></td>
                                            <td>
                                                <?= htmlspecialchars($project['client_name'] ?? 'N/A') ?>
                                                <?php if (($project['client_visibility'] ?? 'yes') === 'no'): ?>
                                                    <span class="badge bg-secondary-subtle text-secondary ms-1">Hidden</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <?php if (($project['status'] ?? 'draft') === 'published'): ?>
                                                    <span class="badge bg-success-subtle text-success">
                                                        <i class="bi bi-check2-circle me-1"></i>Published
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary-subtle text-secondary">
                                                        <i class="bi bi-eye-slash me-1"></i>Draft
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <?php if (!empty($project['featured'])): ?>
                                                    <span class="badge bg-warning-subtle text-warning">
                                                        <i class="bi bi-star-fill me-1"></i>Featured
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge bg-light text-muted">—</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-primary-subtle text-primary">
                                                    <?= (int)($project['sort_order'] ?? 0) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?= !empty($project['created_at']) ? date('M j, Y', strtotime($project['created_at'])) : '—' ?>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="<?= url('admin/projects/' . $project['id'] . '/edit') ?>" class="btn btn-sm btn-outline-primary" title="Edit">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                    <form method="POST" action="<?= url('admin/projects/' . $project['id'] . '/delete') ?>" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this project?')">
                                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
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
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>