<?php

trait SettingsActions {
    // SETTINGS MANAGEMENT
    public function settings() {
        $this->userModel->requireAuth();
        
        $settingGroups = $this->settingModel->getAllGroups();
        $settings = [];
        
        foreach ($settingGroups as $group) {
            $settings[$group] = $this->settingModel->getByGroup($group);
        }
        
        $data = [
            'title' => 'Site Settings - Admin',
            'page' => 'admin-settings',
            'setting_groups' => $settingGroups,
            'settings' => $settings
        ];
        
        $this->render('admin/settings', 'admin', $data);
    }
    
    public function updateSettings() {
        $this->userModel->requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('admin/settings');
            return;
        }
        
        $settingsData = $_POST['settings'] ?? [];
        
        if ($this->settingModel->updateSettings($settingsData)) {
            $this->setFlash('success', 'Settings updated successfully!');
        } else {
            $this->setFlash('error', 'Failed to update settings.');
        }
        
        $this->redirect('admin/settings');
    }
}
