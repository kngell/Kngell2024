<?php

declare(strict_types=1);

use Psr\Log\LoggerInterface;

/**
 * @method string getPageIdentifier(?string $controller = null, ?string $method = null)
 * @method bool deleteCachedPage(string $identifier)
 * @method bool deleteCachedPattern(string $pattern)
 * @method bool deleteAllContexts(string $identifier)
 * @method bool hasHtmlCache()
 * @method LoggerInterface logger()
 */
trait CacheInvalidationTrait
{
    // ============================================
    // CONTROLLER-FRIENDLY INVALIDATION
    // ============================================

    protected function invalidateCache(string $controllerClass, string $action): bool
    {
        $identifier = $this->getPageIdentifier($controllerClass, $action);
        return $this->deleteCachedPage($identifier);
    }

    /**
     * Invalidate multiple caches at once
     * Usage: $this->invalidateCaches([
     *     ShoppingCartController::class => ['index'],
     *     ProductController::class => ['detail', 'list'],
     * ]);.
     */
    protected function invalidateCaches(array $pages): bool
    {
        $success = true;
        foreach ($pages as $controller => $actions) {
            foreach ($actions as $action) {
                if (!$this->invalidateCache($controller, $action)) {
                    $success = false;
                }
            }
        }
        return $success;
    }

    /**
     * Invalidate by identifier directly
     * Usage: $this->invalidateById('shoppingcart_index');.
     */
    protected function invalidateById(string $identifier): bool
    {
        return $this->deleteCachedPage($identifier);
    }

    protected function invalidateByIds(array $identifiers): bool
    {
        $success = true;
        foreach ($identifiers as $identifier) {
            if (!$this->deleteCachedPage($identifier)) {
                $success = false;
            }
        }
        return $success;
    }

    protected function invalidateAll(string $identifier): bool
    {
        return $this->deleteAllContexts($identifier);
    }

    /**
     * Invalidate by pattern
     * Usage: $this->invalidatePattern('html_page_product_*');.
     */
    protected function invalidatePattern(string $pattern): bool
    {
        return $this->deleteCachedPattern($pattern);
    }

    protected function invalidatePatterns(array $patterns): bool
    {
        $success = true;
        foreach ($patterns as $pattern) {
            if (!$this->deleteCachedPattern($pattern)) {
                $success = false;
            }
        }
        return $success;
    }
}