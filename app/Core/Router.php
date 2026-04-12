<?php

namespace App\Core;

class Router {
    protected $routes = [];

    public function add($method, $path, $handler) {
        $path = preg_replace('/\{([a-zA-Z0-9]+)\}/', '(?P<\1>[a-zA-Z0-9\-]+)', $path);
        $this->routes[] = [
            'method' => strtoupper($method),
            'path' => '#^' . $path . '$#',
            'handler' => $handler
        ];
    }

    public function dispatch($method, $uri) {
        $uri = parse_url($uri, PHP_URL_PATH);
        
        foreach ($this->routes as $route) {
            if ($route['method'] === strtoupper($method) && preg_match($route['path'], $uri, $matches)) {
                list($controller, $action) = explode('@', $route['handler']);
                $controllerClass = "App\\Controllers\\$controller";
                
                if (class_exists($controllerClass)) {
                    $controllerInstance = new $controllerClass();
                    if (method_exists($controllerInstance, $action)) {
                        $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                        call_user_func_array([$controllerInstance, $action], $params);
                        return;
                    }
                }
            }
        }
        
        http_response_code(404);
        echo "404 Not Found";
    }
}
