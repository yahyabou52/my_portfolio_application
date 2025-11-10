<?php

require_once ROOT_PATH . '/app/core/helpers.php';
require_once ROOT_PATH . '/app/core/ContentRepository.php';

abstract class BaseController {
    protected $data = [];
    protected $sharedData = [];
    protected $contentRepository;
    
    public function __construct() {
        // Set CSP headers for all requests
        if (!headers_sent()) {
            header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net https://fonts.googleapis.com https://ajax.googleapis.com; style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://fonts.googleapis.com https://fonts.gstatic.com; font-src 'self' https://fonts.gstatic.com https://cdn.jsdelivr.net; img-src 'self' data: https:; connect-src 'self' https:;");
        }

        try {
            $this->contentRepository = new ContentRepository();
            $this->sharedData = $this->contentRepository->getGlobalData();
        } catch (Exception $e) {
            // Fail gracefully if repository cannot be initialized
            $this->contentRepository = null;
            $this->sharedData = [];
        }
    }
    
    protected function view($view, $data = []) {
        $this->data = array_merge($this->sharedData, $this->data, $data);
        
        // Extract data to variables
        extract($this->data);
        
        // Start output buffering
        ob_start();
        
        // Include the view file
        $viewFile = ROOT_PATH . '/app/views/' . $view . '.php';
        
        if (file_exists($viewFile)) {
            include $viewFile;
        } else {
            throw new Exception("View {$view} not found");
        }
        
        // Get the content
        $content = ob_get_clean();
        
        // Return the content
        return $content;
    }
    
    protected function layout($layout, $content, $data = []) {
        $this->data = array_merge($this->sharedData, $this->data, $data);
        $this->data['content'] = $content;
        
        // Extract data to variables
        extract($this->data);
        
        // Include the layout file
        $layoutFile = ROOT_PATH . '/app/views/layouts/' . $layout . '.php';
        
        if (file_exists($layoutFile)) {
            include $layoutFile;
        } else {
            throw new Exception("Layout {$layout} not found");
        }
    }
    
    protected function render($view, $layout = 'main', $data = []) {
        $content = $this->view($view, $data);
        $this->layout($layout, $content, $data);
    }
    
    protected function json($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
    
    protected function redirect($url) {
        header('Location: ' . (strpos($url, 'http') === 0 ? $url : url($url)));
        exit;
    }
    
    protected function setFlash($type, $message) {
        session_start_safe();
        $_SESSION['flash'][$type] = $message;
    }
    
    protected function getFlash($type = null) {
        session_start_safe();
        if ($type) {
            $message = $_SESSION['flash'][$type] ?? null;
            unset($_SESSION['flash'][$type]);
            return $message;
        }
        
        $flash = $_SESSION['flash'] ?? [];
        $_SESSION['flash'] = [];
        return $flash;
    }
    
    protected function hasFlash($type = null) {
        session_start_safe();
        if ($type) {
            return isset($_SESSION['flash'][$type]);
        }
        return !empty($_SESSION['flash']);
    }
}
