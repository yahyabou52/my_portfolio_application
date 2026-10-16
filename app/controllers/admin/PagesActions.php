<?php

trait PagesActions {
    // PAGES MANAGEMENT
    public function pages() {
        $this->userModel->requireAuth();
        $this->redirect('admin/website');
    }
    
    public function editPage($key) {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') { $this->userModel->requireAuth(); $this->redirect('admin/website?group='.urlencode($key)); }
        $this->userModel->requireAuth();
        
        $page = $this->pageModel->getByKey($key);
        if (!$page) {
            $this->setFlash('error', 'Page not found.');
            $this->redirect('admin/pages');
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $pageData = [
                'title' => $_POST['title'] ?? '',
                'meta_description' => $_POST['meta_description'] ?? '',
                'is_active' => isset($_POST['is_active']) ? 1 : 0
            ];
            
            if ($this->pageModel->updateByKey($key, $pageData)) {
                $this->setFlash('success', 'Page updated successfully!');
                $this->redirect('admin/pages');
                return;
            } else {
                $this->setFlash('error', 'Failed to update page.');
            }
        }
        
        $data = [
            'title' => 'Edit Page - Admin',
            'page' => 'admin-page-edit',
            'page_data' => $page
        ];
        
        $this->render('admin/page-form', 'admin', $data);
    }
}
