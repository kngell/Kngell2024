<?php

declare(strict_types=1);

final class MenuItems
{
    public function __construct(
        private FileSearchManager $file,
        private RouteMatchingService $matchingService,
        private AclService $aclService,
    ) {
    }

    public function getMenu(): array
    {
        $user = AuthService::currentUser();

        // Load base menu items
        $baseMenu = (new JsonFile($this->file->get(APP, 'menu_acl.json')))->getContentAsArray();

        // Get all routes for lookup
        $routes = $this->matchingService->getRoutes();

        // Enhance menu with route info
        $enhancedMenu = $this->enhanceMenuWithRoutes($baseMenu, $routes);

        // Filter by ACL
        return $this->aclService->filterByAccess($enhancedMenu, $user, function ($item): array|null|true {
            if (is_array($item) && isset($item['controller'])) {
                return [
                    'controller' => $item['controller'],
                    'action' => $item['action'] ?? 'index',
                ];
            }

            if (is_array($item) && isset($item['type']) && $item['type'] === 'dropdown') {
                return true;
            }

            if ($item === 'separator') {
                return true;
            }

            return null;
        });
    }

    private function enhanceMenuWithRoutes(array $menuItems, array $routes): array
    {
        $enhanced = [];

        foreach ($menuItems as $key => $item) {
            // Handle nested dropdowns with array items
            if (is_array($item) && isset($item['items']) && is_array($item['items'])) {
                $enhanced[$key] = [
                    'type' => $item['type'] ?? 'dropdown',
                    'title' => $this->getTitle($key, $item),
                    'icon' => $item['icon'] ?? null,
                    'items' => $this->enhanceItemsArray($item['items'], $routes),
                ];
            }
            // Handle array menu items with path
            elseif (is_array($item) && isset($item['path'])) {
                $routeInfo = $this->matchingService->findRouteForPath($item['path'], $routes);

                $enhancedItem = [
                    'title' => $this->getTitle($key, $item),
                    'path' => $item['path'],
                ];

                if (isset($item['icon'])) {
                    $enhancedItem['icon'] = $item['icon'];
                }

                if ($routeInfo) {
                    $enhancedItem['controller'] = $this->matchingService->ensureControllerSuffix($routeInfo['controller']);
                    $enhancedItem['action'] = $routeInfo['action'];
                }

                $enhanced[$key] = $enhancedItem;
            }
            // Handle simple string paths
            elseif (is_string($item) && !empty($item)) {
                $routeInfo = $this->matchingService->findRouteForPath($item, $routes);

                if ($routeInfo) {
                    $enhanced[$key] = [
                        'title' => $key,
                        'path' => $item,
                        'controller' => $this->matchingService->ensureControllerSuffix($routeInfo['controller']),
                        'action' => $routeInfo['action'],
                    ];
                } else {
                    // Keep as string if no route found (backward compatibility)
                    $enhanced[$key] = $item;
                }
            } else {
                $enhanced[$key] = $item;
            }
        }

        return $enhanced;
    }

    private function enhanceItemsArray(array $items, array $routes): array
    {
        $enhanced = [];

        foreach ($items as $index => $item) {
            // Handle separators
            if (isset($item['separator']) && $item['separator'] === true) {
                $enhanced[] = 'separator';
            }
            // Handle menu items with key
            elseif (isset($item['key'])) {
                $routeInfo = isset($item['path']) ? $this->matchingService->findRouteForPath($item['path'], $routes) : null;

                $enhancedItem = [
                    'key' => $item['key'],
                    'title' => $item['title'] ?? $item['key'],
                    'path' => $item['path'] ?? '#',
                ];

                if (isset($item['icon'])) {
                    $enhancedItem['icon'] = $item['icon'];
                }

                if ($routeInfo) {
                    $enhancedItem['controller'] = $this->matchingService->ensureControllerSuffix($routeInfo['controller']);
                    $enhancedItem['action'] = $routeInfo['action'];
                }

                $enhanced[] = $enhancedItem;
            }
        }

        return $enhanced;
    }

    private function getTitle(string $key, array $item): string
    {
        return $item['title'] ?? $key;
    }
}