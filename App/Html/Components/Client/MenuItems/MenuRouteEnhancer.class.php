<?php

declare(strict_types=1);

final class MenuRouteEnhancer
{
    public function __construct(
        private RoutePatternRegistry $routeRegistry,
        private CacheInterface $cache,
    ) {
    }

    public function enhanceMenuItems(array $menuItems): array
    {
        $cacheKey = 'menu_route_mappings';

        $routeMappings = $this->cache->get($cacheKey);

        if (!$routeMappings) {
            $routeMappings = $this->buildRouteMappings();
            $this->cache->set($cacheKey, $routeMappings, 3600);
        }

        return $this->mapMenuItems($menuItems, $routeMappings);
    }

    public function buildRouteMappings(): array
    {
        $mappings = [];
        $routes = $this->routeRegistry->getRouteObjects();

        foreach ($routes as $routePath => $route) {
            // Skip group definitions and internal routes
            if ($this->isInternalRoute($routePath)) {
                continue;
            }

            // Get the route object - it might be a Route object or an array
            $routeObj = is_object($route) ? $route : null;

            if (!$routeObj) {
                continue;
            }

            // Extract controller and method
            $controller = $this->extractController($routeObj);
            $method = $this->extractMethod($routeObj);

            if (!$controller || !$method) {
                continue;
            }

            // Generate clean path for matching
            $cleanPath = $this->getCleanPath($routePath);

            // Store mapping
            $mappings[$cleanPath] = [
                'controller' => $controller,
                'action' => $method,
                'httpMethod' => $this->extractHttpMethod($routeObj),
                'middleware' => $this->extractMiddleware($routeObj),
                'routePath' => $routePath, // Keep original for reference
            ];

            // Also map by route name if available
            if (property_exists($routeObj, 'name') && $routeObj->name) {
                $mappings['name:' . $routeObj->name] = &$mappings[$cleanPath];
            }
        }

        return $mappings;
    }

    /**
     * Find a matching route for a given path.
     */
    public function findMatchingRoute(string $searchPath, array $routeMappings): ?array
    {
        // Direct match first
        if (isset($routeMappings[$searchPath])) {
            return $routeMappings[$searchPath];
        }

        // Try to match with parameters
        foreach ($routeMappings as $pattern => $info) {
            if ($this->pathMatchesPattern($searchPath, $pattern)) {
                return $info;
            }
        }

        return null;
    }

    /**
     * Map menu items using the route mappings.
     */
    private function mapMenuItems(array $menuItems, array $routeMappings): array
    {
        $enhanced = [];

        foreach ($menuItems as $key => $item) {
            if (is_array($item)) {
                // Handle nested menus (like "Account")
                $enhanced[$key] = $this->mapMenuItems($item, $routeMappings);
            } elseif ($this->isMenuItemWithPath($key, $item)) {
                // It's a menu item with a path
                $path = $this->normalizePath($item);

                // Find matching route
                $routeInfo = $this->findMatchingRoute($path, $routeMappings);

                if ($routeInfo) {
                    $enhanced[$key] = [
                        'path' => $item,
                        'controller' => $routeInfo['controller'],
                        'action' => $routeInfo['action'],
                    ];
                } else {
                    // Keep original if no match found
                    $enhanced[$key] = $item;
                }
            } else {
                // Keep as is (separators, empty items, etc.)
                $enhanced[$key] = $item;
            }
        }

        return $enhanced;
    }

    /**
     * Check if a path matches a route pattern.
     */
    private function pathMatchesPattern(string $path, string $pattern): bool
    {
        // Convert route pattern to regex
        $regex = preg_replace('/\{[^}]+\}/', '([^/]+)', preg_quote($pattern, '#'));
        $regex = '#^' . $regex . '$#';

        return (bool) preg_match($regex, $path);
    }

    private function extractController(object $route): ?string
    {
        // Try different possible property names
        if (property_exists($route, 'controller')) {
            $controller = $route->controller;
        } elseif (property_exists($route, '_controller')) {
            $controller = $route->_controller;
        } else {
            return null;
        }

        // Ensure Controller suffix
        if ($controller && !str_ends_with($controller, 'Controller')) {
            $controller .= 'Controller';
        }

        return $controller;
    }

    private function extractMethod(object $route): ?string
    {
        if (property_exists($route, 'method')) {
            return $route->method;
        }
        if (property_exists($route, 'action')) {
            return $route->action;
        }
        return null;
    }

    private function extractHttpMethod(object $route): ?string
    {
        if (property_exists($route, 'httpMethod')) {
            return $route->httpMethod;
        }
        return 'get'; // Default to GET
    }

    private function extractMiddleware(object $route): array
    {
        if (property_exists($route, 'middleware')) {
            return (array) $route->middleware;
        }
        return [];
    }

    private function getCleanPath(string $routePath): string
    {
        // Remove parameter placeholders but keep structure
        $path = preg_replace('/\{[^}]+\}/', '*', $routePath);
        return '/' . trim($path, '/');
    }

    private function normalizePath(string $path): string
    {
        return '/' . trim($path, '/');
    }

    private function isMenuItemWithPath(string $key, $item): bool
    {
        // Skip special items
        if ($key === 'separator' || $item === 'separator') {
            return false;
        }

        // Skip empty items
        if (empty($item)) {
            return false;
        }

        // It should be a non-empty string that looks like a path
        return is_string($item) && (str_starts_with($item, '/') || $item === '');
    }

    private function isInternalRoute(string $routePath): bool
    {
        $internalPatterns = [
            '/^_/',
            '/_error/',
            '/_404/',
            '/_500/',
            '/_dev/',
            '/_restrict/',
            '/_client/',
        ];

        foreach ($internalPatterns as $pattern) {
            if (preg_match($pattern, $routePath)) {
                return true;
            }
        }

        return false;
    }
}