<?php

declare(strict_types=1);

class UserAddressCacheManagerFactory extends AbstractHtmlSectionCacheFactory
{
    private const int PAGE_TTL = 3600;
    private const int ENTITY_TTL = 3600;

    private const string CACHE_FOLDER = 'checkout';
    private const string ENTITY_CLASS = UserAddress::class;

    #[Override]
    protected function pageTTl(): int
    {
        return self::PAGE_TTL;
    }

    #[Override]
    protected function entityTtl(): int
    {
        return self::ENTITY_TTL;
    }

    protected function cacheFolder(): string
    {
        return self::CACHE_FOLDER;
    }

    protected function entityClass(): string
    {
        return self::ENTITY_CLASS;
    }

    protected function cacheSubFolder(): string
    {
        return 'address';
    }
}