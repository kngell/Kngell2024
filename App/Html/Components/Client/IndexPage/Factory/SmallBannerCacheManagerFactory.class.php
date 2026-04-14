<?php

declare(strict_types=1);

final class SmallBannerCacheManagerFactory extends AbstractHtmlSectionCacheFactory
{
    private const string CACHE_FOLDER = 'small_banner';
    private const string ENTITY_CLASS = SmallBannerShow::class;

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
        return 'small_banner_sections';
    }
}