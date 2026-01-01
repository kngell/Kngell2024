<?php

declare(strict_types=1);

final class CachingServiceFactory
{
    public static function createProductCachingService(
        CacheInterface $cache,
        EntityFactory $entityFactory,
        ProductShowModel $productModel,
    ): ProductCachingService {
        // Create generic entity caching service
        $entityCache = new EntityCachingService($cache, $entityFactory);

        // Create product-specific caching service
        return new ProductCachingService($entityCache, $productModel);
    }

    public static function createUserCachingService(
        CacheInterface $cache,
        EntityFactory $entityFactory,
        UserModel $userModel,
    ): UserCachingService {
        $entityCache = new EntityCachingService($cache, $entityFactory);
        return new UserCachingService($entityCache, $userModel);
    }

    public static function createEntityCachingService(
        CacheInterface $cache,
        EntityFactory $entityFactory,
    ): EntityCachingService {
        return new EntityCachingService($cache, $entityFactory);
    }
}