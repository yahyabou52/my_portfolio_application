<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">Contact Messages</h1>
                <div class="d-flex gap-2">
                    <!-- Filter Buttons -->
                    <div class="btn-group" role="group">
                        <a href="<?= url('admin/messages') ?>" 
                           class="btn <?= ($current_filter === 'all') ? 'btn-primary' : 'btn-outline-primary' ?> btn-sm">
                            All (<?= $total_messages ?>)
                        </a>
                        <a href="<?= url('admin/messages?filter=unread') ?>" 
                           class="btn <?= ($current_filter === 'unread') ? 'btn-warning' : 'btn-outline-warning' ?> btn-sm">
                            Unread (<?= $unread_messages ?>)
                        </a>
                        <a href="<?= url('admin/messages?filter=read') ?>" 
                           class="btn <?= ($current_filter === 'read') ? 'btn-success' : 'btn-outline-success' ?> btn-sm">
                            Read
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Search -->
    <div class="row mb-4">
        <div class="col-md-6">
            <form method="GET" action="<?= url('admin/messages') ?>" class="d-flex">
                <input type="hidden" name="filter" value="<?= htmlspecialchars($current_filter) ?>">
                <input type="text" class="form-control" name="search" 
                       placeholder="Search messages..." 
                       value="<?= htmlspecialchars($search_query ?? '') ?>">
                <button type="submit" class="btn btn-primary ms-2">
                    <i class="bi bi-search"></i>
                </button>
                <?php if ($search_query): ?>
                    <a href="<?= url('admin/messages?filter=' . $current_filter) ?>" 
                       class="btn btn-outline-secondary ms-2">
                        <i class="bi bi-x"></i>
                    </a>
                <?php endif; ?>
            </form>
        </div>
    </div>
    
    <!-- Messages Table -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-body">
                    <?php if (!empty($messages)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th width="100">Status</th>
                                        <th width="200">From</th>
                                        <th>Subject & Message</th>
                                        <th width="150">Date</th>
                                        <th width="120">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($messages as $message): ?>
                                        <tr class="<?= $message['is_read'] ? '' : 'table-warning' ?>">
                                            <td>
                                                <?php if ($message['is_read']): ?>
                                                    <span class="badge bg-success">
                                                        <i class="bi bi-check-circle"></i> Read
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge bg-warning">
                                                        <i class="bi bi-exclamation-circle"></i> Unread
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="d-flex flex-column">
                                                    <strong class="mb-1"><?= htmlspecialchars($message['name']) ?></strong>
                                                    <small class="text-muted">
                                                        <i class="bi bi-envelope me-1"></i>
                                                        <?= htmlspecialchars($message['email']) ?>
                                                    </small>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="message-content">
                                                    <div class="message-subject fw-bold mb-1">
                                                        <?= htmlspecialchars($message['subject']) ?>
                                                    </div>
                                                    <div class="message-preview text-muted">
                                                        <?= htmlspecialchars(substr($message['message'], 0, 100)) ?>
                                                        <?= strlen($message['message']) > 100 ? '...' : '' ?>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex flex-column">
                                                    <span class="fw-medium"><?= date('M j, Y', strtotime($message['created_at'])) ?></span>
                                                    <small class="text-muted"><?= date('g:i A', strtotime($message['created_at'])) ?></small>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <a href="<?= url('admin/messages/' . $message['id']) ?>" 
                                                       class="btn btn-outline-primary" 
                                                       data-bs-toggle="tooltip" title="View Details">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                    <button type="button" 
                                                            class="btn btn-outline-secondary"
                                                            onclick="toggleReadStatus(<?= $message['id'] ?>, <?= $message['is_read'] ? 'false' : 'true' ?>)"
                                                            data-bs-toggle="tooltip" 
                                                            title="<?= $message['is_read'] ? 'Mark as Unread' : 'Mark as Read' ?>">
                                                        <i class="bi bi-<?= $message['is_read'] ? 'envelope' : 'envelope-open' ?>"></i>
                                                    </button>
                                                    <button type="button" 
                                                            class="btn btn-outline-danger"
                                                            onclick="deleteMessage(<?= $message['id'] ?>)"
                                                            data-bs-toggle="tooltip" title="Delete">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination -->
                        <?php if (isset($pagination)): ?>
                            <nav aria-label="Messages Pagination" class="mt-4">
                                <ul class="pagination justify-content-center">
                                    <!-- Previous Page -->
                                    <li class="page-item <?= !$pagination['has_prev'] ? 'disabled' : '' ?>">
                                        <a class="page-link" 
                                           href="<?= $pagination['has_prev'] ? url('admin/messages?page=' . ($pagination['current_page'] - 1) . '&filter=' . $current_filter . ($search_query ? '&search=' . urlencode($search_query) : '')) : '#' ?>">
                                            <i class="bi bi-chevron-left"></i> Previous
                                        </a>
                                    </li>
                                    
                                    <!-- Page Numbers -->
                                    <?php 
                                    $start_page = max(1, $pagination['current_page'] - 2);
                                    $end_page = min($pagination['total_pages'], $pagination['current_page'] + 2);
                                    
                                    for ($i = $start_page; $i <= $end_page; $i++): 
                                    ?>
                                        <li class="page-item <?= $i === $pagination['current_page'] ? 'active' : '' ?>">
                                            <a class="page-link" 
                                               href="<?= url('admin/messages?page=' . $i . '&filter=' . $current_filter . ($search_query ? '&search=' . urlencode($search_query) : '')) ?>">
                                                <?= $i ?>
                                            </a>
                                        </li>
                                    <?php endfor; ?>
                                    
                                    <!-- Next Page -->
                                    <li class="page-item <?= !$pagination['has_next'] ? 'disabled' : '' ?>">
                                        <a class="page-link" 
                                           href="<?= $pagination['has_next'] ? url('admin/messages?page=' . ($pagination['current_page'] + 1) . '&filter=' . $current_filter . ($search_query ? '&search=' . urlencode($search_query) : '')) : '#' ?>">
                                            Next <i class="bi bi-chevron-right"></i>
                                        </a>
                                    </li>
                                </ul>
                                
                                <div class="text-center text-muted">
                                    Showing <?= count($messages) ?> of <?= $pagination['total'] ?> messages
                                    (Page <?= $pagination['current_page'] ?> of <?= $pagination['total_pages'] ?>)
                                </div>
                            </nav>
                        <?php endif; ?>
                        
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="bi bi-inbox display-4 text-muted mb-3"></i>
                            <h5 class="text-muted">
                                <?php if ($search_query): ?>
                                    No messages found matching "<?= htmlspecialchars($search_query) ?>"
                                <?php elseif ($current_filter === 'unread'): ?>
                                    No unread messages
                                <?php elseif ($current_filter === 'read'): ?>
                                    No read messages
                                <?php else: ?>
                                    No messages yet
                                <?php endif; ?>
                            </h5>
                            <p class="text-muted">
                                <?php if ($search_query): ?>
                                    Try searching with different keywords or
                                    <a href="<?= url('admin/messages?filter=' . $current_filter) ?>">clear the search</a>.
                                <?php else: ?>
                                    When visitors contact you through the website, their messages will appear here.
                                <?php endif; ?>
                            </p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirm Deletion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this message? This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteForm" method="POST" style="display: inline;">
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-trash me-1"></i>
                        Delete Message
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
async function toggleReadStatus(messageId, markAsRead) {
    try {
        const response = await fetch(`<?= url('admin/messages') ?>/${messageId}/toggle-read`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            }
        });
        
        const result = await response.json();
        
        if (result.success) {
            location.reload(); // Refresh to update the UI
        } else {
            alert('Error updating message status: ' + result.message);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error updating message status');
    }
}

function deleteMessage(messageId) {
    // Set the form action
    const deleteForm = document.getElementById('deleteForm');
    deleteForm.action = `<?= url('admin/messages') ?>/${messageId}/delete`;
    
    // Show the modal
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
    deleteModal.show();
}

// Initialize tooltips
document.addEventListener('DOMContentLoaded', function() {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>

<style>
.message-content {
    max-width: 300px;
}

.message-subject {
    max-width: 250px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.message-preview {
    font-size: 0.9rem;
    line-height: 1.4;
}

.table-warning {
    --bs-table-accent-bg: rgba(255, 193, 7, 0.1);
}

.btn-group-sm > .btn, .btn-sm {
    padding: 0.375rem 0.5rem;
}
</style>