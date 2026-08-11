<?php

declare(strict_types=1);

final class FooterAboutCacheManagerFactory extends AbstractHtmlSectionCacheFactory
{
    private const string CACHE_FOLDER = 'footer';
    private const string ENTITY_CLASS = FooterAbout::class;

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
        return 'footer_about';
    }
}