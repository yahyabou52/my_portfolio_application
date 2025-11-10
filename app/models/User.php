<?php

require_once ROOT_PATH . '/app/core/helpers.php';
require_once ROOT_PATH . '/app/core/BaseModel.php';

class User extends BaseModel {
    protected $table = 'admin_users';
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'role',
        'last_login_at'
    ];
    
    public function authenticate($username, $password) {
        $user = $this->findBy('username', $username);
        
        if ($user && password_verify($password, $user['password'])) {
            $this->update($user['id'], ['last_login_at' => date('Y-m-d H:i:s')]);
            return $user;
        }
        
        return false;
    }
    
    public function findByEmail($email) {
        return $this->findBy('email', $email);
    }
    
    public function findByUsername($username) {
        return $this->findBy('username', $username);
    }
    
    public function createUser($data) {
        // Hash the password before storing
        if (isset($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        
        if (empty($data['role'])) {
            $data['role'] = 'editor';
        }

        return $this->create($data);
    }
    
    public function updatePassword($userId, $newPassword) {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        return $this->update($userId, ['password' => $hashedPassword]);
    }
    
    public function validateLogin($data) {
        $errors = [];
        
        if (empty($data['username'])) {
            $errors['username'] = 'Username is required';
        }
        
        if (empty($data['password'])) {
            $errors['password'] = 'Password is required';
        }
        
        return $errors;
    }
    
    public function validateRegistration($data) {
        $errors = [];
        
        if (empty($data['username']) || strlen(trim($data['username'])) < 3) {
            $errors['username'] = 'Username must be at least 3 characters long';
        } elseif ($this->findByUsername($data['username'])) {
            $errors['username'] = 'Username already exists';
        }
        
        if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Please provide a valid email address';
        } elseif ($this->findByEmail($data['email'])) {
            $errors['email'] = 'Email already exists';
        }
        
        if (empty($data['password']) || strlen($data['password']) < 6) {
            $errors['password'] = 'Password must be at least 6 characters long';
        }
        
        if (isset($data['password_confirm']) && $data['password'] !== $data['password_confirm']) {
            $errors['password_confirm'] = 'Passwords do not match';
        }
        
        return $errors;
    }
    
    public function isLoggedIn() {
        session_start_safe();
        return isset($_SESSION['admin_user']);
    }
    
    public function getCurrentUser() {
        session_start_safe();
        if ($this->isLoggedIn()) {
            return $_SESSION['admin_user'];
        }
        return null;
    }
    
    public function login($user) {
        session_start_safe();
        
        // Remove password from session data
        unset($user['password']);
        
        $_SESSION['admin_user'] = $user;
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_login_time'] = time();
        
        return true;
    }
    
    public function logout() {
        session_start_safe();
        
        unset($_SESSION['admin_user']);
        unset($_SESSION['admin_logged_in']);
        unset($_SESSION['admin_login_time']);
        
        return true;
    }
    
    public function requireAuth() {
        if (!$this->isLoggedIn()) {
            redirect('admin/login');
        }
    }
}