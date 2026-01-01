<?php

declare(strict_types=1);

class RoutePatternExporter extends ConfigExporterService
{
    protected string $configType = 'routes';

    public function __construct(
        CacheInterface $cache,
        private RoutePatternConverterService $converter,
        private RouteCollector $routeCollector,
    ) {
        parent::__construct($cache);
    }

    public function exportForClient(string $group = 'all', array $options = []): array
    {
        $source = 'routes.yaml';

        return $this->getCachedOrGenerate($source, function () use ($group, $options) {
            return $this->generatePatterns($group, $options);
        }, $options);
    }

    private function generatePatterns(string $group, array $options): array
    {
        $routes = $this->routeCollector->getRouteObjects();
        $patterns = [];

        foreach ($routes as $path => $route) {
            $patterns[$path] = [
                'path' => $path,
                'php_regex' => $this->converter->toPhpRegex($path),
                'js_regex' => $this->converter->toJsRegex($path),
                'menu_regex' => $this->converter->toMenuMatchRegex($path),
                'controller' => $route->controller ?? null,
                'method' => $route->method ?? null,
            ];
        }

        return [
            'patterns' => $patterns,
            'count' => count($patterns),
            'group' => $group,
        ];
    }
}