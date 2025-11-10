<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">Navigation Menu</h1>
                    <p class="text-muted">Manage your website navigation menu items</p>
                </div>
                <a href="<?= url('admin/navigation/create') ?>" class="btn btn-primary">
                    <i class="bi bi-plus-lg me-2"></i>
                    Add Menu Item
                </a>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-list-ul me-2"></i>
                        Menu Structure
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (empty($menu_tree)): ?>
                        <div class="text-center py-4">
                            <i class="bi bi-list-ul display-1 text-muted"></i>
                            <h5 class="mt-3">No menu items found</h5>
                            <p class="text-muted">Create your first menu item to get started.</p>
                            <a href="<?= url('admin/navigation/create') ?>" class="btn btn-primary">
                                <i class="bi bi-plus-lg me-2"></i>
                                Create Menu Item
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Title</th>
                                        <th>URL</th>
                                        <th>Icon</th>
                                        <th>Order</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($menu_tree as $item): ?>
                                        <tr class="<?= $item['is_active'] ? '' : 'table-secondary' ?>">
                                            <td>
                                                <?php if ($item['icon']): ?>
                                                    <i class="<?= htmlspecialchars($item['icon']) ?> me-2"></i>
                                                <?php endif; ?>
                                                <strong><?= htmlspecialchars($item['title']) ?></strong>
                                            </td>
                                            <td>
                                                <code><?= htmlspecialchars($item['url']) ?></code>
                                            </td>
                                            <td>
                                                <?php if ($item['icon']): ?>
                                                    <code><?= htmlspecialchars($item['icon']) ?></code>
                                                <?php else: ?>
                                                    <span class="text-muted">None</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary"><?= $item['sort_order'] ?></span>
                                            </td>
                                            <td>
                                                <?php if ($item['is_active']): ?>
                                                    <span class="badge bg-success">Active</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">Inactive</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="<?= url('admin/navigation/' . $item['id'] . '/edit') ?>" 
                                                       class="btn btn-outline-primary">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                    <button type="button" 
                                                            class="btn btn-outline-danger"
                                                            onclick="confirmDelete(<?= $item['id'] ?>, '<?= htmlspecialchars($item['title']) ?>')">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                        
                                        <?php if (!empty($item['children'])): ?>
                                            <?php foreach ($item['children'] as $child): ?>
                                                <tr class="<?= $child['is_active'] ? '' : 'table-secondary' ?>">
                                                    <td>
                                                        <span class="ms-4">└─</span>
                                                        <?php if ($child['icon']): ?>
                                                            <i class="<?= htmlspecialchars($child['icon']) ?> me-2"></i>
                                                        <?php endif; ?>
                                                        <?= htmlspecialchars($child['title']) ?>
                                                    </td>
                                                    <td>
                                                        <code><?= htmlspecialchars($child['url']) ?></code>
                                                    </td>
                                                    <td>
                                                        <?php if ($child['icon']): ?>
                                                            <code><?= htmlspecialchars($child['icon']) ?></code>
                                                        <?php else: ?>
                                                            <span class="text-muted">None</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-secondary"><?= $child['sort_order'] ?></span>
                                                    </td>
                                                    <td>
                                                        <?php if ($child['is_active']): ?>
                                                            <span class="badge bg-success">Active</span>
                                                        <?php else: ?>
                                                            <span class="badge bg-secondary">Inactive</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <div class="btn-group btn-group-sm">
                                                            <a href="<?= url('admin/navigation/' . $child['id'] . '/edit') ?>" 
                                                               class="btn btn-outline-primary">
                                                                <i class="bi bi-pencil"></i>
                                                            </a>
                                                            <button type="button" 
                                                                    class="btn btn-outline-danger"
                                                                    onclick="confirmDelete(<?= $child['id'] ?>, '<?= htmlspecialchars($child['title']) ?>')">
                                                                <i class="bi bi-trash"></i>
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
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

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete the menu item <strong id="itemName"></strong>?</p>
                <p class="text-muted">This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteForm" method="POST" style="display: inline;">
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function confirmDelete(id, name) {
    document.getElementById('itemName').textContent = name;
    document.getElementById('deleteForm').action = '<?= url('admin/navigation/') ?>' + id + '/delete';
    
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    modal.show();
}
</script>