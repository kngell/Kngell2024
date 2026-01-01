<?php

declare(strict_types=1);

class AdminNavbarDecorator extends AbstractHtmlDecorator
{
    private const int CACHE_TTL = 3600;

    private ?string $cacheKey = null;

    public function __construct(Controller $controller)
    {
        parent::__construct($controller);
        $this->cacheKey = $this->generateCacheKey();
    }

    public function page(): array
    {
        if ($cached = $this->getCachedNavigation()) {
            return ['menulist' => $cached];
        }

        $navElement = new AsideNavigationSection(
            $this->builder,
            new NavigationConfigParser(),
            new MenuItemFactory(
                $this->builder,
                new IconBuilder(),
                $this->getCurrentPath(),
            ),
        );

        $navigationHtml = $navElement->getSection()->generate();

        // Cache the result
        $this->cacheNavigation($navigationHtml);

        return ['menulist' => $navigationHtml];
    }

    private function getCurrentPath(): string
    {
        return $this->request->getPathFromUri();
    }

    private function generateCacheKey(): string
    {
        $path = $this->getCurrentPath();
        // $userId = $this->getUserId();
        // $role = $this->getUserRole();

        return 'nav_' . md5("{$path}"); //{$userId}_{$role}
    }

    private function getCachedNavigation(): ?string
    {
        if (!function_exists('apcu_fetch')) {
            return null;
        }

        $success = false;
        $cached = apcu_fetch($this->cacheKey, $success);

        return $success ? $cached : null;
    }

    private function cacheNavigation(string $html): void
    {
        if (function_exists('apcu_store')) {
            apcu_store($this->cacheKey, $html, self::CACHE_TTL);
        }
    }

    private function getUserId(): int
    {
        // Implement based on your auth system
        return $this->controller->getUser()?->getId() ?? 0;
    }

    private function getUserRole(): string
    {
        // Implement based on your auth system
        return $this->controller->getUser()?->getRole() ?? 'guest';
    }
}