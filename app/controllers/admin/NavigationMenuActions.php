<?php

trait NavigationMenuActions {
    // NAVIGATION MENU MANAGEMENT
    public function navigation() {
        $this->userModel->requireAuth();
        
        $menuTree = $this->navigationModel->getFullMenuTree();
        
        $data = [
            'title' => 'Navigation Menu - Admin',
            'page' => 'admin-navigation',
            'menu_tree' => $menuTree
        ];
        
        $this->render('admin/navigation', 'admin', $data);
    }
    
    public function createMenuItem() {
        $this->userModel->requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $maxSort = $this->navigationModel->getMaxSortOrder($_POST['parent_id'] ?? null);
            
            $menuData = [
                'title' => $_POST['title'] ?? '',
                'url' => $_POST['url'] ?? '',
                'icon' => $_POST['icon'] ?? '',
                'parent_id' => !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : null,
                'sort_order' => $maxSort + 1,
                'is_active' => isset($_POST['is_active']) ? 1 : 0,
                'target' => $_POST['target'] ?? '_self'
            ];
            
            if ($this->navigationModel->create($menuData)) {
                $this->setFlash('success', 'Menu item created successfully!');
            } else {
                $this->setFlash('error', 'Failed to create menu item.');
            }
            
            $this->redirect('admin/navigation');
            return;
        }
        
        $parentItems = $this->navigationModel->getMenuItems();
        
        $data = [
            'title' => 'Create Menu Item - Admin',
            'page' => 'admin-menu-create',
            'parent_items' => $parentItems
        ];
        
        $this->render('admin/menu-form', 'admin', $data);
    }
    
    public function editMenuItem($id) {
        $this->userModel->requireAuth();
        
        $menuItem = $this->navigationModel->find($id);
        if (!$menuItem) {
            $this->setFlash('error', 'Menu item not found.');
            $this->redirect('admin/navigation');
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $menuData = [
                'title' => $_POST['title'] ?? '',
                'url' => $_POST['url'] ?? '',
                'icon' => $_POST['icon'] ?? '',
                'parent_id' => !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : null,
                'is_active' => isset($_POST['is_active']) ? 1 : 0,
                'target' => $_POST['target'] ?? '_self'
            ];
            
            if ($this->navigationModel->update($id, $menuData)) {
                $this->setFlash('success', 'Menu item updated successfully!');
            } else {
                $this->setFlash('error', 'Failed to update menu item.');
            }
            
            $this->redirect('admin/navigation');
            return;
        }
        
        $parentItems = $this->navigationModel->getMenuItems();
        
        $data = [
            'title' => 'Edit Menu Item - Admin',
            'page' => 'admin-menu-edit',
            'menu_item' => $menuItem,
            'parent_items' => $parentItems
        ];
        
        $this->render('admin/menu-form', 'admin', $data);
    }
    
    public function deleteMenuItem($id) {
        $this->userModel->requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('admin/navigation');
            return;
        }
        
        if ($this->navigationModel->delete($id)) {
            $this->setFlash('success', 'Menu item deleted successfully!');
        } else {
            $this->setFlash('error', 'Failed to delete menu item.');
        }
        
        $this->redirect('admin/navigation');
    }
}
