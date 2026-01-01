<?php

declare(strict_types=1);

final class CurrencyCodeProvider implements CurrencyCodeProviderInterface
{
    private const FALLBACK_CODE = 'EUR';
    private const CACHE_TTL = 3600;
    private const CACHE_PREFIX = 'currency_';

    private array $memoryCache = [];
    private CacheInterface $cache;

    public function __construct(
        private CurrencyResolverInterface $currencyResolver,
    ) {
        $this->cache = $this->createDefaultCache();
    }

    public function getCurrencyCode(int $currencyId): string
    {
        $currency = $this->currencyResolver->getCurrencyById($currencyId);

        if (!$currency) {
            throw new RuntimeException(
                sprintf('Currency with ID "%d" not found.', $currencyId),
            );
        }

        return $currency->getCurrencyCode();
    }

    public function getSystemDefaultCurrencyCode(?string $regionCode = null): string
    {
        if ($regionCode) {
            $currency = $this->currencyResolver->resolveCurrencyForRegion($regionCode);
            if ($currency) {
                return $currency->getCurrencyCode();
            }
        }

        $defaultCurrency = $this->currencyResolver->getDefaultCurrency();
        return $defaultCurrency?->getCurrencyCode() ?? self::FALLBACK_CODE;
    }

    public function isValidCurrency(string $currencyCode): bool
    {
        $currency = $this->currencyResolver->getCurrencyByCode($currencyCode);
        return $currency !== null && $currency->getIsActive();
    }

    public function getCurrencySymbol(string $currencyCode): ?string
    {
        $currency = $this->currencyResolver->getCurrencyByCode($currencyCode);

        if ($currency) {
            return $currency->getCurrencySymbol() ?? $currency->getSymbol();
        }

        // Fallback symbols (keep as is)
        return match ($currencyCode) {
            'USD' => '$',
            // ... rest of fallbacks
            default => $currencyCode,
        };
    }

    public function getCurrencyById(int $currencyId): ?Currency
    {
        return $this->currencyResolver->getCurrencyById($currencyId);
    }

    public function getCurrencyByCode(string $currencyCode): ?Currency
    {
        return $this->currencyResolver->getCurrencyByCode($currencyCode);
    }

    public function clearCurrencyCache(int $currencyId, string $currencyCode): void
    {
        // Clear memory cache
        $keysToClear = [
            self::CACHE_PREFIX . 'by_id_' . $currencyId,
            self::CACHE_PREFIX . 'by_code_' . strtoupper($currencyCode),
        ];

        foreach ($keysToClear as $key) {
            unset($this->memoryCache[$key]);
        }

        // Clear file cache
        foreach ($keysToClear as $key) {
            $this->cache->delete($key);
        }

        // Clear list cache
        $this->cache->delete(self::CACHE_PREFIX . 'all_active');

        // Invalidate by tag if supported
        if (method_exists($this->cache, 'invalidateTags')) {
            $this->cache->invalidateTags(['currency_' . $currencyId]);
        }
    }

    public function clearAllCache(): void
    {
        // Clear memory cache
        $this->memoryCache = [];

        // Clear all currency-related cache files using pattern
        if (method_exists($this->cache, 'deletePattern')) {
            $this->cache->deletePattern(self::CACHE_PREFIX . '*');
        } else {
            // Fallback: get all keys and delete individually
            $keys = $this->cache->getKeys(self::CACHE_PREFIX . '*');
            foreach ($keys as $key) {
                $this->cache->delete($key);
            }
        }
    }

    public function getCacheStats(): array
    {
        $stats = [
            'memory_cache_count' => count($this->memoryCache),
            'memory_cache_keys' => array_keys($this->memoryCache),
        ];

        if (method_exists($this->cache, 'getStats')) {
            $stats['file_cache'] = $this->cache->getStats();
        }

        return $stats;
    }

    public function warmUpCache(): void
    {
        $commonCurrencies = ['USD', 'EUR', 'GBP', 'JPY', 'CAD', 'AUD'];

        foreach ($commonCurrencies as $currencyCode) {
            $this->getCurrencyByCode($currencyCode);
        }

        // Also pre-load the list
        $this->currencyResolver->getAllActiveCurrencies();
    }

    public function clearCache(): void
    {
        $this->memoryCache = [];

        if ($this->cache) {
            $this->cache->deletePattern('currency_*');
        }
    }

    private function saveToCache(string $key, Currency $currency, array $tags = []): void
    {
        // Save to memory cache
        $this->memoryCache[$key] = $currency;

        // Save to file cache with tags if supported
        if (method_exists($this->cache, 'setWithTags')) {
            $this->cache->setWithTags($key, $currency, self::CACHE_TTL, $tags);
        } else {
            $this->cache->set($key, $currency, self::CACHE_TTL);
        }
    }

    private function createDefaultCache(): CacheInterface
    {
        $envConfig = new CacheEnvironmentConfigurations('defaultCache', [
            'cache_path' => DS . 'storage' . DS . 'cache' . DS . 'currency' . DS,
            'default_lifetime' => self::CACHE_TTL,
        ]);

        $storage = new NativeCacheStorage($envConfig, [], new DirectoryManager(), new FileContentManager());
        return new Cache('currency_cache', $storage, []);
    }
}