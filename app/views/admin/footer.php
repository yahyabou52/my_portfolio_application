<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">Footer Management</h1>
                    <p class="text-muted">Manage your website footer content and links</p>
                </div>
            </div>
        </div>
    </div>
    
    <form method="POST" action="<?= url('admin/footer') ?>">
        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-layout-text-window-reverse me-2"></i>
                            Footer Content
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="footer_text" class="form-label">Copyright Text</label>
                            <input type="text" 
                                   class="form-control" 
                                   id="footer_text" 
                                   name="footer_text"
                                   value="<?= htmlspecialchars($footer_data['footer_text'] ?? '') ?>"
                                   placeholder="© 2024 Your Portfolio. All rights reserved.">
                        </div>
                        
                        <div class="mb-3">
                            <label for="footer_additional_text" class="form-label">Additional Text</label>
                            <textarea class="form-control" 
                                      id="footer_additional_text" 
                                      name="footer_additional_text" 
                                      rows="2"
                                      placeholder="Built with passion and creativity."><?= htmlspecialchars($footer_data['footer_additional_text'] ?? '') ?></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       id="footer_show_social" 
                                       name="footer_show_social" 
                                       value="1"
                                       <?= ($footer_data['footer_show_social'] ?? '') ? 'checked' : '' ?>>
                                <label class="form-check-label" for="footer_show_social">
                                    Show Social Media Links
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-eye me-2"></i>
                            Footer Preview
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="footer-preview bg-dark text-white p-3 rounded">
                            <div class="text-center">
                                <p class="mb-1 preview-footer-text"><?= htmlspecialchars($footer_data['footer_text'] ?? '© 2024 Your Portfolio. All rights reserved.') ?></p>
                                <small class="text-light preview-additional-text"><?= htmlspecialchars($footer_data['footer_additional_text'] ?? 'Built with passion and creativity.') ?></small>
                                
                                <?php if (($footer_data['footer_show_social'] ?? '')): ?>
                                    <div class="mt-2">
                                        <i class="bi bi-facebook me-2"></i>
                                        <i class="bi bi-twitter me-2"></i>
                                        <i class="bi bi-linkedin me-2"></i>
                                        <i class="bi bi-instagram"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="mt-3">
                            <small class="text-muted">
                                <i class="bi bi-info-circle me-1"></i>
                                Preview shows basic layout. Actual styling may vary.
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row mt-3">
            <div class="col-12">
                <div class="d-flex justify-content-end gap-2">
                    <a href="<?= url('admin/dashboard') ?>" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg me-2"></i>
                        Update Footer
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
// Live preview updates
document.addEventListener('DOMContentLoaded', function() {
    // Footer text preview
    const footerTextInput = document.getElementById('footer_text');
    const footerTextPreview = document.querySelector('.preview-footer-text');
    
    if (footerTextInput && footerTextPreview) {
        footerTextInput.addEventListener('input', function() {
            footerTextPreview.textContent = this.value || '© 2024 Your Portfolio. All rights reserved.';
        });
    }
    
    // Additional text preview
    const additionalTextInput = document.getElementById('footer_additional_text');
    const additionalTextPreview = document.querySelector('.preview-additional-text');
    
    if (additionalTextInput && additionalTextPreview) {
        additionalTextInput.addEventListener('input', function() {
            additionalTextPreview.textContent = this.value || 'Built with passion and creativity.';
        });
    }
});
</script>