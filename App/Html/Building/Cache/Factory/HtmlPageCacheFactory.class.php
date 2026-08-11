<?php

declare(strict_types=1);

use Psr\Log\LoggerInterface;

class HtmlPageCacheFactory
{
    private const string HTML_CACHE_FOLDER = 'html_cache';
    private const string PAGE_CACHE_FOLDER = 'html_pages';
    private const string SECTION_CACHE_FOLDER = 'sections';

    public function __construct(
        private readonly CacheManager $cacheManager,
        private readonly EntityCacheKeyGeneratorInterface $keyGenerator,
        private readonly ?LoggerInterface $logger = null,
    ) {
    }

    public function createPageCache(): HtmlPageCacheManager
    {
        $cache = $this->createCacheInstance(self::PAGE_CACHE_FOLDER);

        return new HtmlPageCacheManager(
            $cache,
            $this->keyGenerator,
            $this->logger,
        );
    }

    public function createSectionCache(): HtmlPageCacheManager
    {
        $cache = $this->createCacheInstance(self::SECTION_CACHE_FOLDER);

        return new HtmlPageCacheManager(
            $cache,
            $this->keyGenerator,
            $this->logger,
        );
    }

    public function createCombinedCache(): HtmlPageCacheManager
    {
        $cache = $this->createCacheInstance(self::HTML_CACHE_FOLDER);

        return new HtmlPageCacheManager(
            $cache,
            $this->keyGenerator,
            $this->logger,
        );
    }

    public function createCustomCache(string $name, string $subfolder): CacheInterface
    {
        return $this->cacheManager->createCache(
            self::HTML_CACHE_FOLDER . DS . $subfolder . DS . $name,
        );
    }

    public function purgeAll(): bool
    {
        $basePath = $this->cacheManager->getBaseCachePath() . self::HTML_CACHE_FOLDER;

        if (!is_dir($basePath)) {
            return true;
        }

        $success = true;
        $folders = glob($basePath . DS . '*', GLOB_ONLYDIR);

        foreach ($folders as $folder) {
            $cache = $this->cacheManager->createCache(
                self::HTML_CACHE_FOLDER . DS . basename($folder),
            );
            $success = $success && $cache->clear();
        }

        return $success;
    }

    private function createCacheInstance(string $subfolder): CacheInterface
    {
        return $this->cacheManager->createCache(
            self::HTML_CACHE_FOLDER . DS . $subfolder,
        );
    }
}