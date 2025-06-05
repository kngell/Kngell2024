<?php

declare(strict_types=1);

final readonly class RouteCollector
{
    private array $routes;

    public function __construct()
    {
        $this->routes = App::getInstance()->getRoutes();
    }

    /**
     * Get the value of routes.
     *
     * @return array
     */
    public function getRoutes(): array
    {
        return $this->routes;
    }

    public function addRoute(string $method, string $path, $handler): void
    {
        $this->routes[] = [
            'method' => strtoupper($method),
            'path' => $path,
            'handler' => $handler,
        ];
    }

    public function findRoute(string $method, string $path): ?array
    {
        foreach ($this->routes as $route) {
            if ($route['method'] === strtoupper($method) && $route['path'] === $path) {
                return $route;
            }
        }
        return null;
    }

    public function group(string $prefix, callable $callback): void
    {
        // Temporarily store current prefix, call the callback, then restore
    }

    public function addNamedRoute(string $name, string $method, string $path, $handler): void
    {
        $this->routes[$name] = [
            'method' => strtoupper($method),
            'path' => $path,
            'handler' => $handler,
        ];
    }

    public function getRouteByName(string $name): ?array
    {
        return $this->routes[$name] ?? null;
    }
}