<?php

declare(strict_types=1);

final class RegionDataProvider implements RegionDataProviderInterface
{
    private const CACHE_TTL = 3600;
    private const CACHE_PREFIX = 'region_data_';

    public function __construct(
        private RegionModel $regionModel,
        private CacheInterface $cache,
        private string $systemDefaultRegion,
        private EntityFactory $entityFactory,
    ) {
    }

    public function getRegionData(string $regionCode): ?Region
    {
        return $this->getRegionByCode($regionCode);
    }

    public function isValidRegion(string $regionCode): bool
    {
        $region = $this->getRegionByCode($regionCode);
        return $region !== null && $region->getIsActive();
    }

    public function getRegionByCode(string $regionCode): ?Region
    {
        $cacheKey = self::CACHE_PREFIX . strtoupper($regionCode);

        if ($cached = $this->cache->get($cacheKey)) {
            $region = $this->entityFactory->create(Region::class);
            return $region->assign($cached);
        }

        $region = $this->regionModel->one([
            'region_code' => strtoupper($regionCode),
            'is_active' => true,
        ])->asClass();

        if ($region) {
            $this->cache->set($cacheKey, $region->toArray(), self::CACHE_TTL);
        }

        return $region;
    }

    public function getDefaultRegion(): string
    {
        return $this->systemDefaultRegion;
    }

    public function clearRegionCache(string $regionCode): void
    {
        $this->cache->delete(self::CACHE_PREFIX . strtoupper($regionCode));
    }
}