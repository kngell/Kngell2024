<?php

declare(strict_types=1);

final class ProductCardCacheManagerFactory extends AbstractHtmlSectionCacheFactory
{
    private const string CACHE_FOLDER = 'product_cards';
    private const string ENTITY_CLASS = ProductShow::class;

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
        return 'product_sections';
    }
}