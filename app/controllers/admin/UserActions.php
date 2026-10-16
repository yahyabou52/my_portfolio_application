<?php

trait UserActions {
    // USER MANAGEMENT
    public function users() {
        $this->userModel->requireAuth();
        
        $users = $this->userModel->all();
        
        $data = [
            'title' => 'Admin Users - Admin',
            'page' => 'admin-users',
            'users' => $users
        ];
        
        $this->render('admin/users', 'admin', $data);
    }
    
    public function createUser() {
        $this->userModel->requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userData = [
                'username' => $_POST['username'] ?? '',
                'email' => $_POST['email'] ?? '',
                'password' => $_POST['password'] ?? ''
            ];
            
            $errors = $this->userModel->validateRegistration($userData);
            
            if (empty($errors)) {
                if ($this->userModel->createUser($userData)) {
                    $this->setFlash('success', 'User created successfully!');
                    $this->redirect('admin/users');
                    return;
                } else {
                    $this->setFlash('error', 'Failed to create user.');
                }
            } else {
                $this->setFlash('form_errors', $errors);
                $this->setFlash('form_data', $userData);
            }
        }
        
        $data = [
            'title' => 'Create User - Admin',
            'page' => 'admin-user-create'
        ];
        
        $this->render('admin/user-form', 'admin', $data);
    }
    
    public function editUser($id) {
        $this->userModel->requireAuth();
        
        $user = $this->userModel->find($id);
        if (!$user) {
            $this->setFlash('error', 'User not found.');
            $this->redirect('admin/users');
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userData = [
                'username' => $_POST['username'] ?? '',
                'email' => $_POST['email'] ?? ''
            ];
            
            if (!empty($_POST['password'])) {
                $userData['password'] = $_POST['password'];
            }
            
            if ($this->userModel->update($id, $userData)) {
                $this->setFlash('success', 'User updated successfully!');
                $this->redirect('admin/users');
                return;
            } else {
                $this->setFlash('error', 'Failed to update user.');
            }
        }
        
        $data = [
            'title' => 'Edit User - Admin',
            'page' => 'admin-user-edit',
            'user' => $user
        ];
        
        $this->render('admin/user-form', 'admin', $data);
    }
    
    public function deleteUser($id) {
        $this->userModel->requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('admin/users');
            return;
        }
        
        $currentUser = $this->userModel->getCurrentUser();
        if ($id == $currentUser['id']) {
            $this->setFlash('error', 'You cannot delete your own account.');
            $this->redirect('admin/users');
            return;
        }
        
        if ($this->userModel->delete($id)) {
            $this->setFlash('success', 'User deleted successfully!');
        } else {
            $this->setFlash('error', 'Failed to delete user.');
        }
        
        $this->redirect('admin/users');
    }
}
