<?php

declare(strict_types=1);

final class RouteCollector
{
    private array $routes = [];
    private array $groupStack = [];

    public function __construct(array $routes = [])
    {
        $this->loadRoutes($routes);
    }

    public function addRoute(string $path, mixed $params): void
    {
        // Handle empty array routes (like /{controller}/{method}: {})
        if (is_array($params) && empty($params)) {
            $this->processEmptyRoute($path);
            return;
        }

        // Handle numeric key routes (just path without parameters)
        if (is_numeric($path) && is_string($params)) {
            $this->processStringRoute($params, 'default@index');
            return;
        }

        // Ensure params is always an array
        if (!is_array($params)) {
            $params = [];
        }

        $route = $this->mergeWithGroups(['path' => $path] + $params);

        // ✅ FIX: Safely access array keys with null coalescing
        $controller = $route['controller'] ?? null;
        $method = $route['method'] ?? null;
        $httpMethod = $route['httpMethod'] ?? null;
        $middleware = (array) ($route['middleware'] ?? []);
        $responseBody = $route['responseBody'] ?? null;
        $responseStatus = $route['ResponseStatus'] ?? null;

        $this->routes[$route['path']] = new Route(
            path: $route['path'],
            controller: $controller,
            method: $method,
            httpMethod: $httpMethod,
            middleware: $middleware,
            responseBody: $this->createResponseBody($responseBody),
            responseStatus: $this->createResponseStatus($responseStatus),
        );
    }

    /**
     * Get routes as arrays for RouteMatcher compatibility.
     */
    public function getRoutes(): array
    {
        // Debug the first few routes to see their types
        $i = 0;
        foreach ($this->routes as $path => $route) {
            if ($i++ < 3) {
                $type = gettype($route);
                $class = is_object($route) ? get_class($route) : 'not object';
            }
        }

        return $this->routes;
    }

    /**
     * Get Route objects (for internal use).
     */
    public function getRouteObjects(): array
    {
        return $this->routes;
    }

    /**
     * Debug method to check what routes were loaded.
     */
    public function debugRoutes(): array
    {
        $debug = [];
        foreach ($this->routes as $route) {
            $debug[] = [
                'path' => $route->path,
                'controller' => $route->controller,
                'method' => $route->method,
                'middleware' => $route->middleware,
            ];
        }
        return $debug;
    }

    private function createBasicRoute(string $path): void
    {
        $segments = explode('/', trim($path, '/'));
        $controllerName = !empty($segments) ? end($segments) : null;

        $params = [
            'controller' => $controllerName,
            'method' => null,
        ];

        $route = $this->mergeWithGroups(['path' => $path] + $params);

        $this->routes[$route['path']] = new Route(
            path: $route['path'],
            controller: $route['controller'],
            method: $route['method'],
            httpMethod: $route['httpMethod'] ?? null,
            middleware: (array) ($route['middleware'] ?? []),
            responseBody: null,
            responseStatus: null,
        );
    }

    private function inferParametersFromPath(string $path): array
    {
        $params = [];
        $segments = explode('/', trim($path, '/'));

        foreach ($segments as $segment) {
            if (preg_match('/^{([^}]+)}$/', $segment, $matches)) {
                $paramName = $matches[1];
                // Remove regex patterns
                $paramName = preg_replace('/:.*$/', '', $paramName);

                if ($paramName === 'controller') {
                    $params['controller'] = null;
                } elseif ($paramName === 'method' || $paramName === 'action') {
                    $params['method'] = null;
                }
            }
        }

        // Set defaults
        if (!isset($params['controller'])) {
            $params['controller'] = null;
        }
        if (!isset($params['method'])) {
            $params['method'] = null;
        }

        return $params;
    }

    private function processDynamicRoute(string $path): void
    {
        $params = $this->inferParametersFromPath($path);

        $route = $this->mergeWithGroups(['path' => $path] + $params);

        $this->routes[$route['path']] = new Route(
            path: $route['path'],
            controller: $route['controller'] ?? null,
            method: $route['method'] ?? null,
            httpMethod: $route['httpMethod'] ?? null,
            middleware: (array) ($route['middleware'] ?? []),
            responseBody: null,
            responseStatus: null,
        );
    }

    private function processEmptyRoute(string $path): void
    {
        if ($this->isDynamicRoute($path)) {
            // For routes like /{controller}/{method}, we need to ensure they get processed
            $params = $this->inferParametersFromPath($path);

            // Ensure we have at least the basic structure
            $defaultParams = [
                'controller' => null,
                'method' => null,
                'httpMethod' => null,
                'middleware' => [],
            ];

            $params = array_merge($defaultParams, $params);

            $route = $this->mergeWithGroups(['path' => $path] + $params);

            $this->routes[$route['path']] = new Route(
                path: $route['path'],
                controller: $route['controller'] ?? null,
                method: $route['method'] ?? null,
                httpMethod: $route['httpMethod'] ?? null,
                middleware: (array) ($route['middleware'] ?? []),
                responseBody: null,
                responseStatus: null,
            );
        } else {
            $this->createBasicRoute($path);
        }
    }

    private function isDynamicRoute(string $path): bool
    {
        return str_contains($path, '{') && str_contains($path, '}');
    }

    private function loadRoutes(array $routes): void
    {
        foreach ($routes as $key => $config) {
            // Skip entirely if config is null — except when the route is dynamic
            if ($config === null) {
                // If the route path contains dynamic parameters like {controller} or {method},
                // treat null as an empty array so it can be processed as a dynamic route.
                if (is_string($key) && $this->isDynamicRoute((string) $key)) {
                    $config = [];
                } else {
                    continue;
                }
            }

            if ($this->isGroupDefinition($config)) {
                $this->processGroup($key, $config);
            } else {
                $this->addRoute($key, $config);
            }
        }
    }

    private function isGroupDefinition(mixed $config): bool
    {
        // Return false if config is not an array
        if (!is_array($config)) {
            return false;
        }

        return isset($config['_group']) && is_array($config['_group']);
    }

    private function processGroup(string $groupName, array $groupConfig): void
    {
        $groupAttributes = $groupConfig['_group'];

        $this->groupStack[] = [
            'prefix' => $groupAttributes['prefix'] ?? '',
            'middleware' => $groupAttributes['middleware'] ?? [],
        ];

        foreach ($groupConfig as $routeKey => $routeConfig) {
            if ($routeKey === '_group') {
                continue;
            }

            // Skip if routeConfig is null
            if ($routeConfig === null) {
                continue;
            }

            // Handle different route configuration formats
            if (is_string($routeConfig)) {
                $this->processStringRoute($routeKey, $routeConfig);
            } elseif (is_array($routeConfig)) {
                $this->addRoute($routeKey, $routeConfig);
            } else {
                continue;
            }
        }

        array_pop($this->groupStack);
    }

    /**
     * Process routes in "controller@method" string format.
     */
    private function processStringRoute(string $path, string $handler): void
    {
        if (strpos($handler, '@') !== false) {
            [$controller, $method] = explode('@', $handler, 2);
            $params = [
                'controller' => trim($controller),
                'method' => trim($method),
            ];
        } else {
            // If no @, use the string as controller and default method
            $params = [
                'controller' => trim($handler),
                'method' => null,
            ];
        }

        $this->addRoute($path, $params);
    }

    /**
     * Convert responseBody array to ResponseBody object.
     */
    private function createResponseBody(?array $responseBody): ?ResponseBody
    {
        if (empty($responseBody)) {
            return null;
        }

        try {
            $type = ResponseBodyType::from($responseBody['type'] ?? 'RAW');
            $produces = $responseBody['produces'] ?? null;
            return new ResponseBody($type, $produces);
        } catch (Exception $e) {
            return new ResponseBody(ResponseBodyType::RAW, $responseBody['produces'] ?? null);
        }
    }

    /**
     * Convert ResponseStatus array to ResponseStatus object.
     */
    private function createResponseStatus(?array $responseStatus): ?ResponseStatus
    {
        if (empty($responseStatus)) {
            return null;
        }

        try {
            // Handle both enum value and enum case name
            $statusCodeValue = $responseStatus['HttpStatusCode'] ?? 'HTTP_OK';

            // If it's already an enum case name like "HTTP_OK", use it directly
            if (is_string($statusCodeValue) && defined(HttpStatusCode::class . '::' . $statusCodeValue)) {
                $statusCode = HttpStatusCode::{$statusCodeValue};
            } else {
                // Otherwise, try to create from value
                $statusCode = HttpStatusCode::from($statusCodeValue);
            }

            return new ResponseStatus($statusCode);
        } catch (Exception $e) {
            return new ResponseStatus(HttpStatusCode::HTTP_OK);
        }
    }

    private function mergeWithGroups(array $route): array
    {
        // ✅ Ensure base structure exists
        $route = array_merge([
            'path' => '',
            'controller' => null,
            'method' => null,
            'httpMethod' => null,
            'middleware' => [],
            'responseBody' => null,
            'ResponseStatus' => null,
        ], $route);

        foreach (array_reverse($this->groupStack) as $group) {
            $route = $this->applyGroupAttributes($route, $group);
        }
        return $route;
    }

    private function safeArrayGet(array $array, string $key, string $default = 'NOT_SET'): string
    {
        return array_key_exists($key, $array) ?
            (is_string($array[$key]) ? $array[$key] : gettype($array[$key])) :
            $default;
    }

    private function applyGroupAttributes(array $route, array $group): array
    {
        if (!empty($group['prefix'])) {
            $route['path'] = $this->applyPrefix($route['path'], $group['prefix']);
        }

        if (!empty($group['middleware'])) {
            $existingMiddleware = (array) ($route['middleware'] ?? []);
            $groupMiddleware = (array) ($group['middleware'] ?? []);
            $route['middleware'] = array_merge($groupMiddleware, $existingMiddleware);
        }

        // ✅ Ensure we always return the complete structure
        return array_merge([
            'path' => '',
            'controller' => null,
            'method' => null,
            'httpMethod' => null,
            'middleware' => [],
            'responseBody' => null,
            'ResponseStatus' => null,
        ], $route);
    }

    private function applyPrefix(string $path, string $prefix): string
    {
        $prefix = trim($prefix, '/');
        $path = trim($path, '/');

        // Special handling for dynamic routes that start with {
        if (str_starts_with($path, '{')) {
            // For dynamic routes like "/{controller}/{method}",
            // we need to be careful with prefix application
            if ($prefix !== '') {
                return '/' . $prefix . '/' . $path;
            }
        }

        if ($path === '') {
            return '/' . $prefix;
        }

        if ($prefix === '') {
            return '/' . $path;
        }

        return '/' . $prefix . '/' . $path;
    }
}