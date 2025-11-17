<?php

declare(strict_types=1);

namespace App\Core;

final class SimpleRouter
{
    /** @var array<string, array<string, array{0: class-string, 1: string}|callable>> */
    private array $routes = [];

    public function __construct()
    {
    }

    /**
     * @param callable|array{0: class-string, 1: string} $handler
     */
    public function addRoute(string $method, string $route, callable|array $handler): void
    {
        $this->routes[$method][$route] = $handler;
    }

    /**
     * @return array{0: int, 1?: array{0: class-string, 1: string}|callable, 2?: array<string, string>}
     */
    public function dispatch(string $method, string $uri): array
    {
        // Check if exact route exists
        if (isset($this->routes[$method][$uri])) {
            return [1, $this->routes[$method][$uri], []];
        }

        // Check for routes with parameters
        foreach ($this->routes[$method] ?? [] as $route => $handler) {
            $pattern = $this->convertRouteToRegex($route);
            if (preg_match($pattern, $uri, $matches)) {
                array_shift($matches); // Remove full match
                $params = [];
                
                // Extract parameter names from route
                preg_match_all('/\{(\w+)(?::[^}]+)?\}/', $route, $paramNames);
                foreach ($paramNames[1] as $index => $name) {
                    if (isset($matches[$index])) {
                        $params[$name] = $matches[$index];
                    }
                }
                
                return [1, $handler, $params];
            }
        }

        return [0]; // Not found
    }

    private function convertRouteToRegex(string $route): string
    {
        // Convert {id:\d+} to (\d+) and {id} to ([^/]+)
        $pattern = preg_replace('/\{(\w+):([^}]+)\}/', '($2)', $route);
        $pattern = preg_replace('/\{(\w+)\}/', '([^/]+)', $pattern);
        return '#^' . $pattern . '$#';
    }
}
