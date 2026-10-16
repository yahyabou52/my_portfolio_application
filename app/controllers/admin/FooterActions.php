<?php

trait FooterActions {
    // FOOTER MANAGEMENT
    public function footer() {
        $this->userModel->requireAuth();
        $this->redirect('admin/website?group=footer');
    }
    
    public function updateFooter() {
        $this->userModel->requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('admin/footer');
            return;
        }
        
        $footerData = $_POST;
        $footerData['footer_show_social'] = isset($_POST['footer_show_social']) ? '1' : '0';
        unset($footerData['csrf_token']);
        
        if ($this->settingModel->updateSettings($footerData)) {
            $this->setFlash('success', 'Footer updated successfully!');
        } else {
            $this->setFlash('error', 'Failed to update footer.');
        }
        
        $this->redirect('admin/footer');
    }
}
