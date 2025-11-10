<?php $totalSkills = isset($skills) && is_countable($skills) ? count($skills) : 0; ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0 text-gray-800">Skills Management</h1>
                    <p class="mb-0 text-muted">Manage your technical skills and proficiency levels</p>
                </div>
                <a href="<?= url('admin/skills/create') ?>" class="btn btn-primary">
                    <i class="bi bi-plus-lg me-2"></i>
                    Add Skill
                </a>
            </div>
        </div>
    </div>

    <!-- Skills Table -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 fw-semibold text-primary">All Skills</h6>
                    <span class="text-muted small">Total: <?= $totalSkills ?></span>
                </div>
                <div class="card-body">
                    <?php if (empty($skills)): ?>
                        <div class="text-center py-4">
                            <i class="fas fa-cogs fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No skills found</h5>
                            <p class="text-muted">Start by adding your first skill</p>
                            <a href="<?= url('admin/skills/create') ?>" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Add Skill
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Skill Name</th>
                                        <th>Category</th>
                                        <th>Proficiency</th>
                                        <th>Icon</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($skills as $skill): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <?php if (!empty($skill['icon'])): ?>
                                                        <i class="<?= htmlspecialchars($skill['icon']) ?> me-2 text-primary"></i>
                                                    <?php else: ?>
                                                        <i class="bi bi-cpu me-2 text-primary"></i>
                                                    <?php endif; ?>
                                                    <div>
                                                        <strong><?= htmlspecialchars($skill['name']) ?></strong>
                                                        <?php if (!empty($skill['description'])): ?>
                                                            <br><small class="text-muted"><?= htmlspecialchars(substr($skill['description'], 0, 50)) ?>...</small>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge badge-info"><?= htmlspecialchars($skill['category'] ?? 'General') ?></span>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="progress flex-grow-1 me-2" style="height: 20px;">
                                                        <div class="progress-bar bg-primary" role="progressbar" 
                                                             style="width: <?= $skill['proficiency_level'] ?>%" 
                                                             aria-valuenow="<?= $skill['proficiency_level'] ?>" 
                                                             aria-valuemin="0" 
                                                             aria-valuemax="100">
                                                        </div>
                                                    </div>
                                                    <span class="text-muted"><?= $skill['proficiency_level'] ?>%</span>
                                                </div>
                                            </td>
                                            <td>
                                                <?php if (!empty($skill['icon'])): ?>
                                                    <i class="<?= htmlspecialchars($skill['icon']) ?> fa-lg text-primary"></i>
                                                    <code class="ms-2 text-muted"><?= htmlspecialchars($skill['icon']) ?></code>
                                                <?php else: ?>
                                                    <span class="text-muted">No icon</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if (!empty($skill['is_active'])): ?>
                                                    <span class="badge bg-success-subtle text-success">Active</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary-subtle text-secondary">Inactive</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="<?= url('admin/skills/' . $skill['id'] . '/edit') ?>" class="btn btn-sm btn-outline-primary" title="Edit">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                    <form method="POST" action="<?= url('admin/skills/' . $skill['id'] . '/delete') ?>" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this skill?')">
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