<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">Theme Options</h1>
                    <p class="text-muted">Customize your website's appearance and colors</p>
                </div>
            </div>
        </div>
    </div>
    
    <form method="POST" action="<?= url('admin/theme') ?>" data-auto-loading="false">
        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-palette me-2"></i>
                            Color Scheme
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="primary_color" class="form-label">Primary Color</label>
                                    <div class="input-group">
                                        <input type="color"  
                                               class="form-control form-control-color" 
                                               id="primary_color" 
                                               name="primary_color"
                                               value="<?= htmlspecialchars($theme_data['primary_color'] ?? '#7B3FE4') ?>"
                                               title="Choose primary color">
                                        <input type="text" 
                                               class="form-control" 
                                               id="primary_color_text" 
                                               value="<?= htmlspecialchars($theme_data['primary_color'] ?? '#7B3FE4') ?>"
                                               readonly>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="secondary_color" class="form-label">Secondary Color</label>
                                    <div class="input-group">
                                        <input type="color" 
                                               class="form-control form-control-color" 
                                               id="secondary_color" 
                                               name="secondary_color"
                                               value="<?= htmlspecialchars($theme_data['secondary_color'] ?? '#6c757d') ?>"
                                               title="Choose secondary color">
                                        <input type="text" 
                                               class="form-control" 
                                               id="secondary_color_text" 
                                               value="<?= htmlspecialchars($theme_data['secondary_color'] ?? '#6c757d') ?>"
                                               readonly>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="accent_color" class="form-label">Accent Color</label>
                                    <div class="input-group">
                                        <input type="color" 
                                               class="form-control form-control-color" 
                                               id="accent_color" 
                                               name="accent_color"
                                               value="<?= htmlspecialchars($theme_data['accent_color'] ?? '#28a745') ?>"
                                               title="Choose accent color">
                                        <input type="text" 
                                               class="form-control" 
                                               id="accent_color_text" 
                                               value="<?= htmlspecialchars($theme_data['accent_color'] ?? '#28a745') ?>"
                                               readonly>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       id="dark_mode_enabled" 
                                       name="dark_mode_enabled" 
                                       value="1"
                                       <?= ($theme_data['dark_mode_enabled'] ?? '') ? 'checked' : '' ?>>
                                <label class="form-check-label" for="dark_mode_enabled">
                                    Enable Dark Mode Support
                                </label>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="custom_css" class="form-label">Custom CSS</label>
                            <textarea class="form-control font-monospace" 
                                      id="custom_css" 
                                      name="custom_css" 
                                      rows="8"
                                      placeholder="/* Add your custom CSS here */"><?= htmlspecialchars($theme_data['custom_css'] ?? '') ?></textarea>
                            <div class="form-text">
                                Add custom CSS rules to further customize your website's appearance.
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
                            Color Preview
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="theme-preview">
                            <div class="mb-3">
                                <div class="d-flex align-items-center justify-content-between p-3 rounded" 
                                     id="primary-preview" 
                                     style="background-color: <?= htmlspecialchars($theme_data['primary_color'] ?? '#7B3FE4') ?>; color: white;">
                                    <span>Primary Color</span>
                                    <i class="bi bi-star"></i>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <div class="d-flex align-items-center justify-content-between p-3 rounded" 
                                     id="secondary-preview" 
                                     style="background-color: <?= htmlspecialchars($theme_data['secondary_color'] ?? '#6c757d') ?>; color: white;">
                                    <span>Secondary Color</span>
                                    <i class="bi bi-gear"></i>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <div class="d-flex align-items-center justify-content-between p-3 rounded" 
                                     id="accent-preview" 
                                     style="background-color: <?= htmlspecialchars($theme_data['accent_color'] ?? '#28a745') ?>; color: white;">
                                    <span>Accent Color</span>
                                    <i class="bi bi-check-circle"></i>
                                </div>
                            </div>
                            
                            <div class="mt-4">
                                <h6>Sample Buttons</h6>
                                <div class="d-flex gap-2 flex-wrap">
                                    <button type="button" class="btn btn-sm" id="btn-primary-preview">Primary</button>
                                    <button type="button" class="btn btn-sm" id="btn-secondary-preview">Secondary</button>
                                    <button type="button" class="btn btn-sm" id="btn-accent-preview">Accent</button>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-3">
                            <small class="text-muted">
                                <i class="bi bi-info-circle me-1"></i>
                                Colors update in real-time as you change them.
                            </small>
                        </div>
                    </div>
                </div>
                
                <div class="card mt-3">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-lightbulb me-2"></i>
                            Tips
                        </h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled mb-0">
                            <li class="mb-2">
                                <i class="bi bi-check-circle text-success me-2"></i>
                                Use high contrast colors for better accessibility
                            </li>
                            <li class="mb-2">
                                <i class="bi bi-check-circle text-success me-2"></i>
                                Test your colors on different devices
                            </li>
                            <li class="mb-0">
                                <i class="bi bi-check-circle text-success me-2"></i>
                                Consider your brand guidelines
                            </li>
                        </ul>
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
                        Save Theme
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
// Color picker synchronization and live preview
document.addEventListener('DOMContentLoaded', function() {
    const colorInputs = [
        { input: 'primary_color', text: 'primary_color_text', preview: 'primary-preview', btn: 'btn-primary-preview' },
        { input: 'secondary_color', text: 'secondary_color_text', preview: 'secondary-preview', btn: 'btn-secondary-preview' },
        { input: 'accent_color', text: 'accent_color_text', preview: 'accent-preview', btn: 'btn-accent-preview' }
    ];
    
    colorInputs.forEach(item => {
        const colorInput = document.getElementById(item.input);
        const textInput = document.getElementById(item.text);
        const preview = document.getElementById(item.preview);
        const btn = document.getElementById(item.btn);
        
        // Sync color picker with text input
        colorInput.addEventListener('input', function() {
            textInput.value = this.value;
            preview.style.backgroundColor = this.value;
            btn.style.backgroundColor = this.value;
            btn.style.borderColor = this.value;
            btn.style.color = 'white';
        });
        
        // Initial button styling
        btn.style.backgroundColor = colorInput.value;
        btn.style.borderColor = colorInput.value;
        btn.style.color = 'white';
    });
});
</script>