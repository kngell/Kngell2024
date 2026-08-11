<?php

declare(strict_types=1);

final class MenuItemsService
{
    private const CACHE_TTL = 3600;
    private const MENU_FILE = 'menu_acl.json';

    public function __construct(
        private FileSearchManager $file,
        private RouteMatchingService $matchingService,
        private AclService $aclService,
        private UserContext $userContext,
        private CacheInterface $cache,
    ) {
    }

    public function getMenu(): array
    {
        $user = $this->userContext->currentUser();
        $userId = $user?->getUserId() ?? 'guest';

        $cacheKey = 'menu_' . $userId;

        $cached = $this->cache->get($cacheKey);
        if ($cached !== null) {
            return $cached;
        }

        $menu = $this->buildMenu($user);
        $this->cache->set($cacheKey, $menu, self::CACHE_TTL);

        return $menu;
    }

    public function clearUserCache(int $userId): void
    {
        $this->cache->delete('menu_' . $userId);
    }

    public function clearAllCache(): void
    {
        if (method_exists($this->cache, 'deletePattern')) {
            $this->cache->deletePattern('menu_*');
        }
    }

    public function getMenuUncached(): array
    {
        $user = $this->userContext->currentUser();
        return $this->buildMenu($user);
    }

    private function buildMenu(?User $user): array
    {
        $baseMenu = $this->loadBaseMenu();
        $routes = $this->matchingService->getRoutes();
        $enhancedMenu = $this->enhanceMenuWithRoutes($baseMenu, $routes);
        return $this->filterMenuByAccess($enhancedMenu, $user);
    }

    private function loadBaseMenu(): array
    {
        $menuFile = $this->file->get(APP, self::MENU_FILE);
        return (new JsonFile($menuFile))->getContentAsArray();
    }

    private function filterMenuByAccess(array $menu, ?User $user): array
    {
        return $this->aclService->filterByAccess($menu, $user, function ($item) {
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
            // Handle dropdowns
            if (is_array($item) && isset($item['type']) && $item['type'] === 'dropdown') {
                $enhanced[$key] = [
                    'type' => 'dropdown',
                    'title' => $item['title'] ?? $key,
                    'icon' => $item['icon'] ?? null,
                    'items' => $this->enhanceItemsArray($item['items'] ?? [], $routes),
                ];

                // Only keep dropdown if it has visible items after enhancement
                if (empty($enhanced[$key]['items'])) {
                    unset($enhanced[$key]);
                }
            }
            // Handle array items with path
            elseif (is_array($item) && isset($item['path'])) {
                $routeInfo = $this->resolveRoute($item['path'], $routes);

                $enhancedItem = [
                    'path' => $item['path'],
                    'title' => $item['title'] ?? $key,
                ];

                if (isset($item['icon'])) {
                    $enhancedItem['icon'] = $item['icon'];
                }

                if ($routeInfo) {
                    $enhancedItem['controller'] = $routeInfo['controller'];
                    $enhancedItem['action'] = $routeInfo['action'];
                }

                $enhanced[$key] = $enhancedItem;
            }
            // Handle simple string paths
            elseif (is_string($item) && !empty($item)) {
                $routeInfo = $this->resolveRoute($item, $routes);

                if ($routeInfo) {
                    $enhanced[$key] = [
                        'path' => $item,
                        'title' => $key,
                        'controller' => $routeInfo['controller'],
                        'action' => $routeInfo['action'],
                    ];
                } else {
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

        foreach ($items as $item) {
            if (isset($item['separator']) && $item['separator'] === true) {
                $enhanced[] = 'separator';
                continue;
            }

            if (isset($item['path'])) {
                $routeInfo = $this->resolveRoute($item['path'], $routes);

                $enhancedItem = [
                    'key' => $item['key'] ?? 'item',
                    'title' => $item['title'] ?? $item['key'] ?? 'Item',
                    'path' => $item['path'],
                ];

                if (isset($item['icon'])) {
                    $enhancedItem['icon'] = $item['icon'];
                }

                if ($routeInfo) {
                    $enhancedItem['controller'] = $routeInfo['controller'];
                    $enhancedItem['action'] = $routeInfo['action'];
                }

                $enhanced[] = $enhancedItem;
            }
        }

        return $enhanced;
    }

    private function resolveRoute(string $path, array $routes): ?array
    {
        $routeInfo = $this->matchingService->findRouteForPath($path, $routes);

        if ($routeInfo) {
            return [
                'controller' => $this->matchingService->ensureControllerSuffix($routeInfo['controller']),
                'action' => $routeInfo['action'],
            ];
        }

        return null;
    }
}