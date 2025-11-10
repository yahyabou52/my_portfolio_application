<?php

require_once ROOT_PATH . '/app/core/BaseController.php';
require_once ROOT_PATH . '/app/models/User.php';
require_once ROOT_PATH . '/app/models/Message.php';

class AdminController extends BaseController {
    private $userModel;
    private $messageModel;
    
    public function __construct() {
        parent::__construct();
        $this->userModel = new User();
        $this->messageModel = new Message();
    }
    
    public function redirectToAdmin() {
        // If not logged in, redirect to login
        if (!$this->userModel->isLoggedIn()) {
            $this->redirect('admin/login');
            return;
        }
        
        // If logged in, redirect to dashboard
        $this->redirect('admin/dashboard');
    }
    
    public function login() {
        // If already logged in, redirect to dashboard
        if ($this->userModel->isLoggedIn()) {
            $this->redirect('admin/dashboard');
            return;
        }
        
        $data = [
            'title' => 'Admin Login',
            'page' => 'admin-login'  
        ];
        
        $this->render('admin/login', 'admin', $data);
    }
    
    public function authenticate() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('admin/login');
            return;
        }
        
        $username = trim($_POST['username'] ?? '');
        $password = trim($_POST['password'] ?? '');
        
        $errors = $this->userModel->validateLogin(['username' => $username, 'password' => $password]);
        
        if (!empty($errors)) {
            $this->setFlash('error', 'Please fill in all fields.');
            $this->redirect('admin/login');
            return;
        }
        
        $user = $this->userModel->authenticate($username, $password);
        
        if ($user) {
            $this->userModel->login($user);
            $this->setFlash('success', 'Welcome back, ' . $user['username'] . '!');
            $this->redirect('admin/dashboard');
        } else {
            $this->setFlash('error', 'Invalid username or password.');
            $this->redirect('admin/login');
        }
    }
    
    public function dashboard() {
        $this->userModel->requireAuth();
        
        // Get dashboard statistics
        $totalMessages = $this->messageModel->count();
        $unreadMessages = $this->messageModel->getUnreadCount();
        $recentMessages = $this->messageModel->getRecent(5);
        
        $data = [
            'title' => 'Admin Dashboard',
            'page' => 'admin-dashboard',
            'total_messages' => $totalMessages,
            'unread_messages' => $unreadMessages,
            'recent_messages' => $recentMessages
        ];
        
        $this->render('admin/dashboard', 'admin', $data);
    }
    
    public function messages() {
        $this->userModel->requireAuth();
        
        $page = (int)($_GET['page'] ?? 1);
        $perPage = 10;
        $search = $_GET['search'] ?? '';
        $filter = $_GET['filter'] ?? 'all'; // all, read, unread
        
        if ($search) {
            $messages = $this->messageModel->search($search);
            $pagination = null;
        } else {
            $conditions = [];
            if ($filter === 'read') {
                $conditions['is_read'] = 1;
            } elseif ($filter === 'unread') {
                $conditions['is_read'] = 0;
            }
            
            $result = $this->messageModel->paginate($page, $perPage, $conditions, 'created_at DESC');
            $messages = $result['data'];
            $pagination = $result;
        }
        
        // Get statistics for the header
        $totalMessages = $this->messageModel->count();
        $unreadMessages = $this->messageModel->getUnreadCount();
        
        $data = [
            'title' => 'Contact Messages - Admin',
            'page' => 'admin-messages',
            'messages' => $messages,
            'pagination' => $pagination,
            'current_filter' => $filter,
            'search_query' => $search,
            'total_messages' => $totalMessages,
            'unread_messages' => $unreadMessages
        ];
        
        $this->render('admin/messages', 'admin', $data);
    }
    
    public function messageDetail($id) {
        $this->userModel->requireAuth();
        
        $message = $this->messageModel->find($id);
        
        if (!$message) {
            $this->setFlash('error', 'Message not found.');
            $this->redirect('admin/messages');
            return;
        }
        
        // Mark as read when viewing
        if (!$message['is_read']) {
            $this->messageModel->markAsRead($id);
        }
        
        $data = [
            'title' => 'Message Details - Admin',
            'page' => 'admin-message-detail',
            'message' => $message
        ];
        
        $this->render('admin/message-detail', 'admin', $data);
    }
    
    public function toggleRead($id) {
        $this->userModel->requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('admin/messages');
            return;
        }
        
        $message = $this->messageModel->find($id);
        
        if (!$message) {
            $this->json(['success' => false, 'message' => 'Message not found'], 404);
            return;
        }
        
        $newStatus = $message['is_read'] ? 0 : 1;
        $success = $this->messageModel->update($id, ['is_read' => $newStatus]);
        
        if ($success) {
            $statusText = $newStatus ? 'read' : 'unread';
            $this->json([
                'success' => true, 
                'message' => "Message marked as {$statusText}",
                'is_read' => $newStatus
            ]);
        } else {
            $this->json(['success' => false, 'message' => 'Failed to update message'], 500);
        }
    }
    
    public function deleteMessage($id) {
        $this->userModel->requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('admin/messages');
            return;
        }
        
        $message = $this->messageModel->find($id);
        
        if (!$message) {
            $this->setFlash('error', 'Message not found.');
            $this->redirect('admin/messages');
            return;
        }
        
        $success = $this->messageModel->delete($id);
        
        if ($success) {
            $this->setFlash('success', 'Message deleted successfully.');
        } else {
            $this->setFlash('error', 'Failed to delete message.');
        }
        
        $this->redirect('admin/messages');
    }
    
    public function logout() {
        $this->userModel->logout();
        $this->setFlash('success', 'You have been logged out successfully.');
        $this->redirect('admin/login');
    }
}