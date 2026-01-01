<?php

declare(strict_types=1);

final class ProductCachingFactory
{
    public static function create(
        CacheInterface $cache,
        EntityCachedDataInterface $entityCachedData,
        SmartSerializerInterface $serializer,
        ProductShowModel $productModel,
    ): ProductCachingService {
        // Create generic entity caching service
        $entityCache = new EntityCachingService($cache, $entityCachedData, $serializer);

        // Create product-specific caching service
        return new ProductCachingService($entityCache, $productModel, $cache);
    }
}