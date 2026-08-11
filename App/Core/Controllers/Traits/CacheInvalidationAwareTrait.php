<?php

declare(strict_types=1);
/**
 * @method FlashInterface getFlash()
 * @method bool invalidateCache()
 */
trait CacheInvalidationAwareTrait
{
    protected function invalidateCacheIfFlagged(string $controllerClass, string $method): void
    {
        if ($this->getFlash()->consumeFlag(FlashFlagKey::INVALIDATE_CACHE)) {
            $this->logTiming('Cache invalidation flag detected');

            $this->invalidateCacheEntry(
                controller: $controllerClass,
                method: $method,
            );

            $this->logTiming('Cache invalidated for: ' . $controllerClass . '::' . $method);
        }
    }

    protected function hasCacheInvalidationFlag(): bool
    {
        return $this->getFlash()->hasFlag(FlashFlagKey::INVALIDATE_CACHE);
    }

    protected function debugCacheInvalidationFlag(): void
    {
        $flags = $this->getFlash()->getAllFlags();
        if (!empty($flags)) {
            $this->logTiming('Current flags: ' . implode(', ', $flags));
        }
    }
}