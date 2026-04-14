<?php

declare(strict_types=1);

final class HeroCacheManagerFactory extends AbstractHtmlSectionCacheFactory
{
    private const string CACHE_FOLDER = 'hero';
    private const string ENTITY_CLASS = Hero::class;

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
        return 'hero_sections'; // Organize by page sections
    }
}