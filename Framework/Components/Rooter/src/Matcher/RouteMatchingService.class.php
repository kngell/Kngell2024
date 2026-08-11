<?php

declare(strict_types=1);

final class RouteMatchingService
{
    public function __construct(
        private RoutePatternRegistry $patternRegistry,
    ) {
    }

    public function findRouteForPath(string $path, array $routes): ?array
    {
        $normalizedPath = trim($path, '/');

        foreach ($routes as $routePath => $route) {
            if (!$route instanceof Route) {
                continue;
            }
            $pattern = $this->patternRegistry->getPhpPattern($routePath);

            if (preg_match($pattern, $normalizedPath, $matches)) {
                $namedMatches = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

                $controller = $route->controller ?? ($namedMatches['controller'] ?? null);
                $method = $route->method ?? ($namedMatches['method'] ?? null);

                if ($controller && $method) {
                    return [
                        'controller' => $controller,
                        'action' => $method,
                        'route' => $route,
                        'matches' => $namedMatches,
                    ];
                }
            }
        }

        return null;
    }

    public function extractController(array $routeInfo): string
    {
        $controller = $routeInfo['controller'] ?? '';
        return $this->ensureControllerSuffix($controller);
    }

    public function ensureControllerSuffix(?string $controller): ?string
    {
        if (!$controller) {
            return null;
        }

        $controller = preg_replace('/Controller$/', '', $controller);
        return $controller . 'Controller';
    }

    public function getRoutes(): array
    {
        return $this->patternRegistry->getRouteObjects();
    }
}