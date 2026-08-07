<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Central URL routing engine with regex-based matching.
 *
 * Supports GET, POST, PUT, DELETE methods, route groups,
 * and middleware chains.
 */
class Router
{
    /** @var array<string, array<string, array{handler: callable|array, middleware: list<string>, pattern: string}>> */
    private array $routes = [];

    /** @var array<string, list<string>> */
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

    /**
     * Group routes under a common prefix.
     *
     * @param string $prefix URL prefix
     * @param callable $callback Receives this Router instance
     * @param list<string> $middleware Middleware applied to all routes in group
     */
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

    /**
     * Register a route.
     */
    private function addRoute(string $method, string $path, callable|array $handler, array $middleware): self
    {
        $fullPath = $this->currentGroup . $path;
        if ($fullPath === '') $fullPath = '/';

        $this->routes[$method][$fullPath] = [
            'handler' => $handler,
            'middleware' => array_merge(
                $this->middleware[$this->currentGroup] ?? [],
                $middleware
            ),
            'pattern' => $this->convertToRegex($fullPath),
        ];

        return $this;
    }

    /**
     * Convert a route path to a regex pattern.
     */
    private function convertToRegex(string $path): string
    {
        $pattern = preg_replace('/\{([a-zA-Z_]+)\}/', '(?P<$1>[a-zA-Z0-9_-]+)', $path);
        // Make trailing slash optional
        if (str_ends_with($pattern, '/')) {
            $pattern = substr($pattern, 0, -1) . '/?';
        }
        return '#^' . $pattern . '$#';
    }

    /**
     * Dispatch an incoming request to the matching route.
     */
    public function dispatch(string $method, string $uri): void
    {
        $uri = parse_url($uri, PHP_URL_PATH) ?: '/';
        $uri = rtrim($uri, '/') ?: '/';

        foreach ($this->routes[$method] ?? [] as $routePath => $route) {
            $matches = [];
            $normalizedRoute = rtrim($routePath, '/') ?: '/';

            if ($uri === $normalizedRoute || preg_match($route['pattern'], $uri, $matches) || preg_match($route['pattern'], $uri . '/', $matches)) {
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

                // Execute middleware chain
                foreach ($route['middleware'] as $middlewareClass) {
                    $middlewareInstance = new $middlewareClass();
                    if (!$middlewareInstance->handle()) {
                        return;
                    }
                }

                // Execute handler
                $handler = $route['handler'];

                if (is_array($handler)) {
                    [$controllerClass, $actionMethod] = $handler;
                    $controller = new $controllerClass();
                    $controller->$actionMethod(...array_values($params));
                } else {
                    $handler(...array_values($params));
                }

                return;
            }
        }

        // No route matched
        http_response_code(404);
        echo '<h1>404 - Page Not Found</h1>';
    }
}
