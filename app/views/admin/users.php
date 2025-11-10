<?php session_start_safe(); ?>

<div class="container-fluid">
    <div class="row align-items-center mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h3 mb-0">Admin Users</h1>
                <p class="text-muted mb-0">Manage administrator accounts and access</p>
            </div>
            <a href="<?= url('admin/users/create') ?>" class="btn btn-primary">
                <i class="bi bi-person-plus me-2"></i>
                Add User
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">User Directory</h5>
        </div>
        <div class="card-body p-0">
            <?php if (!empty($users)): ?>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th scope="col">User</th>
                                <th scope="col">Email</th>
                                <th scope="col" class="text-center">Role</th>
                                <th scope="col" class="text-center">Created</th>
                                <th scope="col" class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="avatar-placeholder rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center">
                                                <?= strtoupper(substr($user['username'], 0, 1)) ?>
                                            </div>
                                            <div>
                                                <h6 class="mb-0"><?= htmlspecialchars($user['username']) ?></h6>
                                                <small class="text-muted">ID: <?= (int)$user['id'] ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?= htmlspecialchars($user['email']) ?></td>
                                    <td class="text-center">
                                        <span class="badge bg-secondary-subtle text-secondary text-uppercase">
                                            <?= htmlspecialchars($user['role'] ?? 'admin') ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <small class="text-muted">
                                            <?= !empty($user['created_at']) ? date('M j, Y', strtotime($user['created_at'])) : '—' ?>
                                        </small>
                                    </td>
                                    <td class="text-end">
                                        <div class="btn-group" role="group">
                                            <a href="<?= url('admin/users/' . $user['id'] . '/edit') ?>" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <?php $currentUser = $_SESSION['admin_user']['id'] ?? null; ?>
                                            <?php if ($currentUser !== (int)$user['id']): ?>
                                                <form method="POST" action="<?= url('admin/users/' . $user['id'] . '/delete') ?>" class="d-inline" onsubmit="return confirm('Delete this user?');">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="p-4 text-center text-muted">
                    <i class="bi bi-people fs-1 d-block mb-2"></i>
                    <p class="mb-0">No admin users found. Create your first user to get started.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.avatar-placeholder {
    width: 42px;
    height: 42px;
    font-weight: 600;
}
</style>
