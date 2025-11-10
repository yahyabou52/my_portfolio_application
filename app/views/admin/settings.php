<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">Site Settings</h1>
                    <p class="text-muted">Manage your website's global settings and configuration</p>
                </div>
            </div>
        </div>
    </div>
    
    <form method="POST" action="<?= url('admin/settings') ?>">
        <div class="row">
            <?php foreach ($setting_groups as $group): ?>
                <div class="col-lg-6 mb-4">
                    <div class="card h-100">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-<?= $group === 'general' ? 'gear' : ($group === 'contact' ? 'envelope' : ($group === 'social' ? 'share' : 'info-circle')) ?> me-2"></i>
                                <?= ucfirst(str_replace('_', ' ', $group)) ?> Settings
                            </h5>
                        </div>
                        <div class="card-body">
                            <?php foreach ($settings[$group] as $setting): ?>
                                <div class="mb-3">
                                    <label for="<?= $setting['setting_key'] ?>" class="form-label">
                                        <?= ucwords(str_replace(['_', 'site_', 'contact_', 'social_', 'hero_', 'about_'], ' ', $setting['setting_key'])) ?>
                                    </label>
                                    
                                    <?php if ($setting['setting_type'] === 'textarea'): ?>
                                        <textarea class="form-control" 
                                                  id="<?= $setting['setting_key'] ?>" 
                                                  name="settings[<?= $setting['setting_key'] ?>]" 
                                                  rows="3"
                                                  <?= !$setting['is_editable'] ? 'readonly' : '' ?>><?= htmlspecialchars($setting['setting_value']) ?></textarea>
                                    
                                    <?php elseif ($setting['setting_type'] === 'boolean'): ?>
                                        <div class="form-check">
                                            <input class="form-check-input" 
                                                   type="checkbox" 
                                                   id="<?= $setting['setting_key'] ?>" 
                                                   name="settings[<?= $setting['setting_key'] ?>]" 
                                                   value="1"
                                                   <?= $setting['setting_value'] ? 'checked' : '' ?>
                                                   <?= !$setting['is_editable'] ? 'disabled' : '' ?>>
                                            <label class="form-check-label" for="<?= $setting['setting_key'] ?>">
                                                Enable
                                            </label>
                                        </div>
                                    
                                    <?php elseif ($setting['setting_type'] === 'url'): ?>
                                        <input type="url" 
                                               class="form-control" 
                                               id="<?= $setting['setting_key'] ?>" 
                                               name="settings[<?= $setting['setting_key'] ?>]"
                                               value="<?= htmlspecialchars($setting['setting_value']) ?>"
                                               placeholder="https://example.com"
                                               <?= !$setting['is_editable'] ? 'readonly' : '' ?>>
                                    
                                    <?php elseif ($setting['setting_type'] === 'email'): ?>
                                        <input type="email" 
                                               class="form-control" 
                                               id="<?= $setting['setting_key'] ?>" 
                                               name="settings[<?= $setting['setting_key'] ?>]"
                                               value="<?= htmlspecialchars($setting['setting_value']) ?>"
                                               <?= !$setting['is_editable'] ? 'readonly' : '' ?>>
                                    
                                    <?php else: ?>
                                        <input type="text" 
                                               class="form-control" 
                                               id="<?= $setting['setting_key'] ?>" 
                                               name="settings[<?= $setting['setting_key'] ?>]"
                                               value="<?= htmlspecialchars($setting['setting_value']) ?>"
                                               <?= !$setting['is_editable'] ? 'readonly' : '' ?>>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-end gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg me-2"></i>
                        Save Settings
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>