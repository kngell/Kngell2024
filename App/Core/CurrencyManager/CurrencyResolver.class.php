<?php

declare(strict_types=1);

final class CurrencyResolver implements CurrencyResolverInterface
{
    private const CACHE_TTL = 3600;
    private const CACHE_PREFIX = 'currency_resolver_';

    private CacheInterface $cache;

    public function __construct(
        private RegionModel $regionModel,
        private CurrencyModel $currencyModel,
        private EntityFactory $entityFactory,
    ) {
        $this->cache = $this->createDefaultCache();
    }

    public function resolveCurrencyForRegion(string $regionCode): ?Currency
    {
        $cacheKey = self::CACHE_PREFIX . 'region_' . strtoupper($regionCode);

        if ($cached = $this->cache->get($cacheKey)) {
            return $this->entityFactory->createFromDatabase(Currency::class, $cached);
        }

        // Get region
        $region = $this->regionModel->one([
            'region_code' => strtoupper($regionCode),
            'is_active' => true,
        ])->asClass();

        if (!$region) {
            return $this->getDefaultCurrency();
        }

        // Get region's currency
        if ($region->getCurrencyId()) {
            $currency = $this->getCurrencyById($region->getCurrencyId());
            if ($currency) {
                $this->cache->set($cacheKey, $currency->toArray(), self::CACHE_TTL);
                return $currency;
            }
        }

        // Fallback to default
        $default = $this->getDefaultCurrency();
        $this->cache->set($cacheKey, $default, self::CACHE_TTL);

        return $default;
    }

    public function getDefaultCurrency(): ?Currency
    {
        $cacheKey = self::CACHE_PREFIX . 'default';

        if ($cached = $this->cache->get($cacheKey)) {
            $currency = App::diget(Currency::class);
            return $currency->assign($cached);
        }

        $currency = $this->currencyModel->one([
            'is_default' => true,
            'is_active' => true,
        ])->asClass();

        if (!$currency) {
            $currency = $this->currencyModel->one([
                'currency_code' => 'EUR',
                'is_active' => true,
            ])->asClass();
        }

        if ($currency) {
            $this->cache->set($cacheKey, $currency->toArray(), self::CACHE_TTL);
        }

        return $currency;
    }

    public function getAllActiveCurrencies(): array
    {
        $cacheKey = self::CACHE_PREFIX . 'all_active';

        // Check file cache
        $currencies = $this->cache->get($cacheKey);
        if (is_array($currencies)) {
            return $currencies;
        }

        // Query database
        $currencies = $this->currencyModel->all(['is_active' => true])->asClass();

        // Cache result with TTL
        $this->cache->set($cacheKey, $currencies, self::CACHE_TTL);

        return $currencies;
    }

    public function getCurrencyById(int $currencyId): ?Currency
    {
        $cacheKey = self::CACHE_PREFIX . 'id_' . $currencyId;

        if ($cached = $this->cache->get($cacheKey)) {
            $currency = App::diget(Currency::class);
            return $currency->assign($cached);
        }

        $currency = $this->currencyModel->find($currencyId)->asClass();

        if ($currency) {
            $this->cache->set($cacheKey, $currency->toArray(), self::CACHE_TTL);
        }

        return $currency;
    }

    public function getCurrencyByCode(string $currencyCode): ?Currency
    {
        $cacheKey = self::CACHE_PREFIX . 'code_' . strtoupper($currencyCode);

        if ($cached = $this->cache->get($cacheKey)) {
            return $this->entityFactory->createFromDatabase(Currency::class, $cached);
        }

        $currency = $this->currencyModel->one([
            'currency_code' => strtoupper($currencyCode),
            'is_active' => true,
        ])->asClass();

        if ($currency) {
            $this->cache->set($cacheKey, $currency->toArray(), self::CACHE_TTL);
        }

        return $currency;
    }

    public function clearRegionCache(string $regionCode): void
    {
        $this->cache->delete(self::CACHE_PREFIX . 'region_' . strtoupper($regionCode));
    }

    public function clearAllCache(): void
    {
        if (method_exists($this->cache, 'deletePattern')) {
            $this->cache->deletePattern(self::CACHE_PREFIX . '*');
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