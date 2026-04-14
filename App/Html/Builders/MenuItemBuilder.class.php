<?php

declare(strict_types=1);

final class MenuItemBuilder
{
    public function __construct(
        private RouteMatchingService $matchingService,
    ) {
    }

    /**
     * Build a menu item with all necessary data.
     */
    public function buildItem(string $key, array|string $config, array $routes): array
    {
        if (is_string($config)) {
            // Simple string path
            return $this->buildFromString($key, $config, $routes);
        }

        // Array config
        return $this->buildFromArray($key, $config, $routes);
    }

    /**
     * Build a dropdown menu.
     */
    public function buildDropdown(string $key, array $config, array $routes): array
    {
        return [
            'type' => $config['type'] ?? 'dropdown',
            'title' => $config['title'] ?? $key,
            'icon' => $config['icon'] ?? null,
            'items' => $this->buildItemsArray($config['items'] ?? [], $routes),
        ];
    }

    private function buildFromString(string $key, string $path, array $routes): array
    {
        $routeInfo = $this->matchingService->findRouteForPath($path, $routes);

        $item = [
            'title' => $key,
            'path' => $path,
        ];

        if ($routeInfo) {
            $item['controller'] = $this->matchingService->ensureControllerSuffix($routeInfo['controller']);
            $item['action'] = $routeInfo['action'];
        }

        return $item;
    }

    private function buildFromArray(string $key, array $config, array $routes): array
    {
        $routeInfo = isset($config['path'])
            ? $this->matchingService->findRouteForPath($config['path'], $routes)
            : null;

        $item = [
            'title' => $config['title'] ?? $key,
            'path' => $config['path'] ?? '#',
        ];

        if (isset($config['icon'])) {
            $item['icon'] = $config['icon'];
        }

        if ($routeInfo) {
            $item['controller'] = $this->matchingService->ensureControllerSuffix($routeInfo['controller']);
            $item['action'] = $routeInfo['action'];
        }

        return $item;
    }

    private function buildItemsArray(array $items, array $routes): array
    {
        $enhanced = [];

        foreach ($items as $item) {
            if (isset($item['separator']) && $item['separator'] === true) {
                $enhanced[] = 'separator';
            } elseif (isset($item['key'])) {
                $enhanced[] = $this->buildFromArray($item['key'], $item, $routes);
            }
        }

        return $enhanced;
    }
}