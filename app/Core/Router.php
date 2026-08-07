<?php

declare(strict_types=1);

namespace App\Core;

class Router
{
    private array $routes = [];
    private array $middleware = [];
    private string $currentGroup = '';

    public function get(string $path, callable|array $handler, array $middleware = []): self
    {
        return $this->addRoute('GET', $path, $handler, $middleware);
    }

    public function post(string $path, callable|array $handler, array $middleware = []): self
    {
        return $this->addRoute('POST', $path, $handler, $middleware);
    }

    public function put(string $path, callable|array $handler, array $middleware = []): self
    {
        return $this->addRoute('PUT', $path, $handler, $middleware);
    }

    public function delete(string $path, callable|array $handler, array $middleware = []): self
    {
        return $this->addRoute('DELETE', $path, $handler, $middleware);
    }

    public function group(string $prefix, callable $callback, array $middleware = []): void
    {
        $previousGroup = $this->currentGroup;
        $this->currentGroup .= $prefix;
        
        if (!empty($middleware)) {
            $this->middleware[$this->currentGroup] = $middleware;
        }
        
        $callback($this);
        
        $this->currentGroup = $previousGroup;
    }

    private function addRoute(string $method, string $path, callable|array $handler, array $middleware): self
    {
        $fullPath = $this->currentGroup . $path;
        
        $this->routes[$method][$fullPath] = [
            'handler' => $handler,
            'middleware' => array_merge(
                $this->middleware[$this->currentGroup] ?? [],
                $middleware
            ),
            'pattern' => $this->convertToRegex($fullPath)
        ];
        
        return $this;
    }

    private function convertToRegex(string $path): string
    {
        $pattern = preg_replace('/\{([a-zA-Z_]+)\}/', '(?P<$1>[a-zA-Z0-9_-]+)', $path);
        return '#^' . $pattern . '$#';
    }

    public function dispatch(string $method, string $uri): void
    {
        $uri = parse_url($uri, PHP_URL_PATH);
        $uri = rtrim($uri, '/') ?: '/';

        foreach ($this->routes[$method] ?? [] as $route) {
            if (preg_match($route['pattern'], $uri, $matches)) {
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                
                foreach ($route['middleware'] as $middlewareClass) {
                    $middleware = new $middlewareClass();
                    if (!$middleware->handle()) {
                        http_response_code(403);
                        echo json_encode(['error' => 'Unauthorized']);
                        return;
                    }
                }
                
                $handler = $route['handler'];
                
                if (is_array($handler)) {
                    [$controller, $method] = $handler;
                    $controller = new $controller();
                    $controller->$method(...$params);
                } else {
                    $handler(...$params);
                }
                
                return;
            }
        }

        http_response_code(404);
        echo json_encode(['error' => 'Route not found']);
    }
}
