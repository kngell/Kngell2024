<?php

declare(strict_types=1);

class RoutePatternRegistry
{
    private array $routes = [];
    private array $compiledPatterns = [];

    public function __construct(
        private RoutePatternConverterService $converter,
        private RouteCollector $routeCollector,
        private CacheInterface $cache,
    ) {
        $this->loadRoutes();
    }

    public function getPhpPattern(string $routePath): string
    {
        if (isset($this->compiledPatterns[$routePath])) {
            return $this->compiledPatterns[$routePath];
        }

        return $this->converter->toPhpRegex($routePath);
    }

    /**
     * Get route objects (for RouteMatcher).
     */
    public function getRouteObjects(): array
    {
        return $this->routes;
    }

    /**
     * Get patterns for JavaScript.
     */
    public function getPatternsForJs(): array
    {
        $patterns = [];

        foreach ($this->routes as $routePath => $route) {
            $patterns[$routePath] = [
                'php_regex' => $this->getPhpPattern($routePath),
                'js_regex' => $this->converter->toJsRegex($routePath),
                'menu_regex' => $this->converter->toMenuMatchRegex($routePath),
                'controller' => $route->controller ?? null,
                'method' => $route->method ?? null,
            ];
        }

        return $patterns;
    }

    private function loadRoutes(): void
    {
        $cacheKey = 'route_patterns_registry';

        if ($cached = $this->cache->get($cacheKey)) {
            $this->routes = $cached['routes'];
            $this->compiledPatterns = $cached['compiled'];
            return;
        }

        // Load routes once
        $this->routes = $this->routeCollector->getRouteObjects();

        foreach ($this->routes as $routePath => $routeObject) {
            // Store in the SAME format as before
            $this->compiledPatterns[$routePath] = $this->converter->toPhpRegex($routePath);
        }

        // Cache
        $this->cache->set($cacheKey, [
            'routes' => $this->routes,
            'compiled' => $this->compiledPatterns,
        ], 3600);
    }
}