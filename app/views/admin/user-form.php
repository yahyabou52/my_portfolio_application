<?php
session_start_safe();

$formErrors = $_SESSION['flash']['form_errors'] ?? [];
$formData = $_SESSION['flash']['form_data'] ?? [];
unset($_SESSION['flash']['form_errors'], $_SESSION['flash']['form_data']);

$isEdit = isset($user);
$actionUrl = $isEdit
    ? url('admin/users/' . $user['id'] . '/edit')
    : url('admin/users/create');

$values = array_merge(
    $user ?? [],
    $formData
);
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h3 mb-0"><?= $isEdit ? 'Edit User' : 'Create User' ?></h1>
                <p class="text-muted mb-0"><?= $isEdit ? 'Update administrator account details.' : 'Invite a new administrator to the dashboard.' ?></p>
            </div>
            <a href="<?= url('admin/users') ?>" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-2"></i>
                Back to Users
            </a>
        </div>
    </div>

    <?php if (!empty($formErrors)): ?>
        <div class="alert alert-danger">
            <h6 class="alert-heading">Please fix the following:</h6>
            <ul class="mb-0 ps-3">
                <?php foreach ($formErrors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="<?= $actionUrl ?>" class="row g-4">
                <div class="col-md-6">
                    <label for="username" class="form-label">Username</label>
                    <input type="text"
                           class="form-control"
                           id="username"
                           name="username"
                           value="<?= htmlspecialchars($values['username'] ?? '') ?>"
                           placeholder="admin"
                           required>
                </div>

                <div class="col-md-6">
                    <label for="email" class="form-label">Email Address</label>
                    <input type="email"
                           class="form-control"
                           id="email"
                           name="email"
                           value="<?= htmlspecialchars($values['email'] ?? '') ?>"
                           placeholder="admin@example.com"
                           required>
                </div>

                <div class="col-md-6">
                    <label for="password" class="form-label">Password <?= $isEdit ? '<span class="text-muted">(leave blank to keep current)</span>' : '' ?></label>
                    <input type="password"
                           class="form-control"
                           id="password"
                           name="password"
                           placeholder="********"
                           <?= $isEdit ? '' : 'required' ?>>
                </div>

                <div class="col-md-6">
                    <label for="password_confirm" class="form-label">Confirm Password <?= $isEdit ? '<span class="text-muted">(optional)</span>' : '' ?></label>
                    <input type="password"
                           class="form-control"
                           id="password_confirm"
                           name="password_confirm"
                           placeholder="********"
                           <?= $isEdit ? '' : 'required' ?>>
                </div>

                <div class="col-12">
                    <div class="alert alert-info mb-0">
                        <div class="d-flex align-items-start">
                            <i class="bi bi-shield-lock me-2"></i>
                            <div>
                                <strong>Security tip:</strong> Use a unique, strong password and share it securely with your team member.
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 d-flex justify-content-end gap-2">
                    <a href="<?= url('admin/users') ?>" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg me-2"></i>
                        <?= $isEdit ? 'Save Changes' : 'Create User' ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
