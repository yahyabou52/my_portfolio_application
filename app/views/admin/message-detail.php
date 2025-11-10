<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="d-flex align-items-center">
                    <a href="<?= url('admin/messages') ?>" class="btn btn-outline-secondary me-3">
                        <i class="bi bi-arrow-left"></i> Back to Messages
                    </a>
                    <h1 class="h3 mb-0">Message Details</h1>
                </div>
                <div class="btn-group" role="group">
                    <button type="button" 
                            class="btn btn-outline-primary"
                            onclick="toggleReadStatus(<?= $message['id'] ?>, <?= $message['is_read'] ? 'false' : 'true' ?>)">
                        <i class="bi bi-<?= $message['is_read'] ? 'envelope' : 'envelope-open' ?> me-1"></i>
                        Mark as <?= $message['is_read'] ? 'Unread' : 'Read' ?>
                    </button>
                    <button type="button" 
                            class="btn btn-outline-danger"
                            onclick="deleteMessage(<?= $message['id'] ?>)">
                        <i class="bi bi-trash me-1"></i> Delete
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-lg-8">
            <!-- Message Content -->
            <div class="card shadow">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-envelope me-2"></i>
                        <?= htmlspecialchars($message['subject']) ?>
                    </h5>
                    <?php if ($message['is_read']): ?>
                        <span class="badge bg-success">
                            <i class="bi bi-check-circle"></i> Read
                        </span>
                    <?php else: ?>
                        <span class="badge bg-warning">
                            <i class="bi bi-exclamation-circle"></i> Unread
                        </span>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <div class="message-body">
                        <?= nl2br(htmlspecialchars($message['message'])) ?>
                    </div>
                    
                    <?php if (isset($message['attachments']) && !empty($message['attachments'])): ?>
                        <hr>
                        <h6><i class="bi bi-paperclip me-2"></i>Attachments</h6>
                        <div class="attachments">
                            <?php foreach ($message['attachments'] as $attachment): ?>
                                <div class="attachment-item mb-2">
                                    <a href="<?= url('admin/messages/' . $message['id'] . '/attachment/' . $attachment['id']) ?>" 
                                       class="btn btn-outline-secondary btn-sm">
                                        <i class="bi bi-download me-1"></i>
                                        <?= htmlspecialchars($attachment['filename']) ?>
                                        <small class="text-muted">(<?= formatFileSize($attachment['size']) ?>)</small>
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <?php if (isset($reply_sent) && $reply_sent): ?>
                <div class="alert alert-success mt-3" role="alert">
                    <i class="bi bi-check-circle me-2"></i>
                    Your reply has been sent successfully!
                </div>
            <?php endif; ?>
            
            <!-- Reply Form -->
            <div class="card shadow mt-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-reply me-2"></i>
                        Send Reply
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="<?= url('admin/messages/' . $message['id'] . '/reply') ?>">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="reply_to" class="form-label">To:</label>
                                <input type="email" class="form-control" id="reply_to" name="reply_to" 
                                       value="<?= htmlspecialchars($message['email']) ?>" readonly>
                            </div>
                            <div class="col-md-6">
                                <label for="reply_subject" class="form-label">Subject:</label>
                                <input type="text" class="form-control" id="reply_subject" name="reply_subject" 
                                       value="Re: <?= htmlspecialchars($message['subject']) ?>" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="reply_message" class="form-label">Message:</label>
                            <textarea class="form-control" id="reply_message" name="reply_message" 
                                      rows="8" placeholder="Type your reply here..." required></textarea>
                        </div>
                        
                        <div class="d-flex justify-content-between">
                            <button type="button" class="btn btn-outline-secondary" onclick="loadTemplate()">
                                <i class="bi bi-file-text me-1"></i>
                                Load Template
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-send me-1"></i>
                                Send Reply
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <!-- Sender Information -->
            <div class="card shadow">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-person me-2"></i>
                        Sender Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="sender-info">
                        <div class="info-item mb-3">
                            <label class="fw-bold text-muted small">Name:</label>
                            <div><?= htmlspecialchars($message['name']) ?></div>
                        </div>
                        
                        <div class="info-item mb-3">
                            <label class="fw-bold text-muted small">Email:</label>
                            <div>
                                <a href="mailto:<?= htmlspecialchars($message['email']) ?>" class="text-decoration-none">
                                    <?= htmlspecialchars($message['email']) ?>
                                </a>
                            </div>
                        </div>
                        
                        <?php if (!empty($message['phone'])): ?>
                            <div class="info-item mb-3">
                                <label class="fw-bold text-muted small">Phone:</label>
                                <div>
                                    <a href="tel:<?= htmlspecialchars($message['phone']) ?>" class="text-decoration-none">
                                        <?= htmlspecialchars($message['phone']) ?>
                                    </a>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <div class="info-item mb-3">
                            <label class="fw-bold text-muted small">Received:</label>
                            <div>
                                <?= date('F j, Y \a\t g:i A', strtotime($message['created_at'])) ?>
                            </div>
                        </div>
                        
                        <div class="info-item mb-3">
                            <label class="fw-bold text-muted small">Status:</label>
                            <div>
                                <?php if ($message['is_read']): ?>
                                    <span class="badge bg-success">Read</span>
                                    <?php if (isset($message['read_at']) && $message['read_at']): ?>
                                        <br><small class="text-muted">
                                            Read on <?= date('M j, Y \a\t g:i A', strtotime($message['read_at'])) ?>
                                        </small>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="badge bg-warning">Unread</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <?php if (!empty($message['ip_address'])): ?>
                            <div class="info-item mb-3">
                                <label class="fw-bold text-muted small">IP Address:</label>
                                <div class="font-monospace small">
                                    <?= htmlspecialchars($message['ip_address']) ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($message['user_agent'])): ?>
                            <div class="info-item">
                                <label class="fw-bold text-muted small">Browser:</label>
                                <div class="small text-muted">
                                    <?= htmlspecialchars(substr($message['user_agent'], 0, 60)) ?>
                                    <?= strlen($message['user_agent']) > 60 ? '...' : '' ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Quick Actions -->
            <div class="card shadow mt-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-lightning me-2"></i>
                        Quick Actions
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="mailto:<?= htmlspecialchars($message['email']) ?>?subject=Re: <?= urlencode($message['subject']) ?>" 
                           class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-envelope me-1"></i>
                            Open in Email Client
                        </a>
                        
                        <?php if (!empty($message['phone'])): ?>
                            <a href="tel:<?= htmlspecialchars($message['phone']) ?>" 
                               class="btn btn-outline-success btn-sm">
                                <i class="bi bi-telephone me-1"></i>
                                Call Sender
                            </a>
                        <?php endif; ?>
                        
                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="copyToClipboard('<?= htmlspecialchars($message['email']) ?>')">
                            <i class="bi bi-clipboard me-1"></i>
                            Copy Email
                        </button>
                        
                        <button type="button" class="btn btn-outline-info btn-sm" onclick="exportMessage(<?= $message['id'] ?>)">
                            <i class="bi bi-download me-1"></i>
                            Export as PDF
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Related Messages -->
            <?php if (isset($related_messages) && !empty($related_messages)): ?>
                <div class="card shadow mt-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-collection me-2"></i>
                            Other Messages from This Sender
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="list-group list-group-flush">
                            <?php foreach ($related_messages as $related): ?>
                                <a href="<?= url('admin/messages/' . $related['id']) ?>" 
                                   class="list-group-item list-group-item-action border-0 px-0">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1"><?= htmlspecialchars($related['subject']) ?></h6>
                                            <small class="text-muted">
                                                <?= date('M j, Y', strtotime($related['created_at'])) ?>
                                            </small>
                                        </div>
                                        <?php if (!$related['is_read']): ?>
                                            <span class="badge bg-warning ms-2">New</span>
                                        <?php endif; ?>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
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
                <div class="bg-light p-3 rounded">
                    <strong>From:</strong> <?= htmlspecialchars($message['name']) ?> &lt;<?= htmlspecialchars($message['email']) ?>&gt;<br>
                    <strong>Subject:</strong> <?= htmlspecialchars($message['subject']) ?>
                </div>
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

<!-- Template Modal -->
<div class="modal fade" id="templateModal" tabindex="-1" aria-labelledby="templateModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="templateModalLabel">Select Reply Template</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="card template-card" onclick="selectTemplate('thank_you')">
                            <div class="card-body">
                                <h6 class="card-title">Thank You</h6>
                                <p class="card-text small">Standard thank you response for inquiries</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="card template-card" onclick="selectTemplate('consultation')">
                            <div class="card-body">
                                <h6 class="card-title">Consultation Request</h6>
                                <p class="card-text small">Response for project consultation requests</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="card template-card" onclick="selectTemplate('quote_request')">
                            <div class="card-body">
                                <h6 class="card-title">Quote Request</h6>
                                <p class="card-text small">Response for pricing inquiries</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="card template-card" onclick="selectTemplate('follow_up')">
                            <div class="card-body">
                                <h6 class="card-title">Follow Up</h6>
                                <p class="card-text small">Following up on previous communication</p>
                            </div>
                        </div>
                    </div>
                </div>
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
            location.reload();
        } else {
            alert('Error updating message status: ' + result.message);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error updating message status');
    }
}

function deleteMessage(messageId) {
    const deleteForm = document.getElementById('deleteForm');
    deleteForm.action = `<?= url('admin/messages') ?>/${messageId}/delete`;
    
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
    deleteModal.show();
}

function loadTemplate() {
    const templateModal = new bootstrap.Modal(document.getElementById('templateModal'));
    templateModal.show();
}

function selectTemplate(templateType) {
    const templates = {
        thank_you: `Hi <?= htmlspecialchars($message['name']) ?>,

Thank you for reaching out! I received your message and appreciate your interest in my services.

I will review your requirements and get back to you within 24 hours with more information.

If you have any urgent questions, please don't hesitate to contact me directly.

Best regards,
[Your Name]`,
        
        consultation: `Hi <?= htmlspecialchars($message['name']) ?>,

Thank you for your interest in my UI/UX design services!

I'd love to discuss your project in more detail. Based on your message, I believe I can help you create an exceptional user experience.

Would you be available for a free 30-minute consultation call this week? We can discuss:
- Your project goals and requirements
- Timeline and budget considerations  
- My design process and approach
- Next steps

Please let me know your availability and I'll send you a calendar link.

Looking forward to working with you!

Best regards,
[Your Name]`,
        
        quote_request: `Hi <?= htmlspecialchars($message['name']) ?>,

Thank you for your interest in my design services!

To provide you with an accurate quote, I'll need some additional information about your project:

- Project scope and deliverables
- Timeline requirements
- Target audience and goals
- Any specific design preferences or constraints
- Budget range

Once I have these details, I can prepare a detailed proposal for you within 2-3 business days.

Would you be available for a brief call to discuss these details?

Best regards,
[Your Name]`,
        
        follow_up: `Hi <?= htmlspecialchars($message['name']) ?>,

I hope this message finds you well. I wanted to follow up on our previous conversation about your project.

I'm still very interested in working with you and wanted to see if you had any questions or if there's anything I can clarify about my services.

If you'd like to move forward or need any additional information, please don't hesitate to reach out.

Best regards,
[Your Name]`
    };
    
    const messageTextarea = document.getElementById('reply_message');
    messageTextarea.value = templates[templateType];
    
    // Close the modal
    const templateModal = bootstrap.Modal.getInstance(document.getElementById('templateModal'));
    templateModal.hide();
}

function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        // Show temporary success feedback
        const button = event.target.closest('button');
        const originalText = button.innerHTML;
        button.innerHTML = '<i class="bi bi-check me-1"></i>Copied!';
        button.classList.remove('btn-outline-secondary');
        button.classList.add('btn-success');
        
        setTimeout(() => {
            button.innerHTML = originalText;
            button.classList.remove('btn-success');
            button.classList.add('btn-outline-secondary');
        }, 2000);
    });
}

function exportMessage(messageId) {
    // This would trigger a server-side PDF generation
    window.open(`<?= url('admin/messages') ?>/${messageId}/export-pdf`, '_blank');
}

// Auto-resize textarea
document.addEventListener('DOMContentLoaded', function() {
    const textarea = document.getElementById('reply_message');
    textarea.addEventListener('input', function() {
        this.style.height = 'auto';
        this.style.height = this.scrollHeight + 'px';
    });
});
</script>

<style>
.message-body {
    font-size: 1rem;
    line-height: 1.6;
    white-space: pre-wrap;
    word-wrap: break-word;
}

.sender-info .info-item {
    border-bottom: 1px solid #f8f9fa;
    padding-bottom: 0.75rem;
}

.sender-info .info-item:last-child {
    border-bottom: none;
    padding-bottom: 0;
}

.template-card {
    cursor: pointer;
    transition: all 0.2s;
    border: 2px solid transparent;
}

.template-card:hover {
    border-color: var(--bs-primary);
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.attachment-item {
    padding: 0.5rem;
    border: 1px solid #dee2e6;
    border-radius: 0.375rem;
    background-color: #f8f9fa;
}

#reply_message {
    resize: vertical;
    min-height: 200px;
}
</style>