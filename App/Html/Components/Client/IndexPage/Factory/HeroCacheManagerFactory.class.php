<?php

declare(strict_types=1);

final class HeroCacheManagerFactory extends AbstractHtmlSectionCacheFactory
{
    private const string CACHE_FOLDER = 'hero';
    private const string ENTITY_CLASS = Hero::class;

    #[Override]
    protected function pageTTl(): int
    {
        return 3600;
    }

    #[Override]
    protected function entityTtl(): int
    {
        return 3600;
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
        return 'hero_sections'; // Organize by page sections
    }
}