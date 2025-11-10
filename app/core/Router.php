<?php

class Router {
    private $routes = [];
    private $currentRoute = null;
    
    public function get($uri, $controller, $method = 'index') {
        $this->routes['GET'][$uri] = ['controller' => $controller, 'method' => $method];
        return $this;
    }
    
    public function post($uri, $controller, $method = 'store') {
        $this->routes['POST'][$uri] = ['controller' => $controller, 'method' => $method];
        return $this;
    }
    
    public function resolve() {
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $method = $_SERVER['REQUEST_METHOD'];
        
        // Remove base path if running in subdirectory
        $basePath = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
        if ($basePath !== '/' && strpos($uri, $basePath) === 0) {
            $uri = substr($uri, strlen($basePath));
        }
        
        $uri = $uri ?: '/';
        
        // Check for exact match first
        if (isset($this->routes[$method][$uri])) {
            $this->currentRoute = $this->routes[$method][$uri];
            return $this->callController($this->currentRoute);
        }
        
        // Check for parameterized routes
        foreach ($this->routes[$method] ?? [] as $route => $target) {
            $pattern = preg_replace('/\{[^}]+\}/', '([^/]+)', $route);
            $pattern = '#^' . $pattern . '$#';
            
            if (preg_match($pattern, $uri, $matches)) {
                array_shift($matches); // Remove full match
                $this->currentRoute = $target;
                return $this->callController($target, $matches);
            }
        }
        
        // 404 Not Found
        http_response_code(404);
        include ROOT_PATH . '/app/views/errors/404.php';
        exit;
    }
    
    private function callController($route, $params = []) {
        $controllerName = $route['controller'];
        $method = $route['method'];
        
        $controllerFile = ROOT_PATH . '/app/controllers/' . $controllerName . '.php';
        
        if (!file_exists($controllerFile)) {
            throw new Exception("Controller {$controllerName} not found");
        }
        
        require_once $controllerFile;
        
        if (!class_exists($controllerName)) {
            throw new Exception("Controller class {$controllerName} not found");
        }
        
        $controller = new $controllerName();
        
        if (!method_exists($controller, $method)) {
            throw new Exception("Method {$method} not found in {$controllerName}");
        }
        
        return call_user_func_array([$controller, $method], $params);
    }
}