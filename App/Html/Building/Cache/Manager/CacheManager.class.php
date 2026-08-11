<?php

declare(strict_types=1);

use Psr\Log\LoggerInterface;

final class CacheManager
{
    private const string BASE_CAHCE_PATH = STORAGE . 'cache' . DS;

    public function __construct(
        private SmartSerializerInterface $serializer,
        private ?string $baseCachePath = null,
        private ?string $cacheFolder = null,
    ) {
    }

    public function createCache(?string $subfolder = null): CacheInterface
    {
        $fullCachePath = $this->baseCachePath;
        if (empty($fullCachePath)) {
            $fullCachePath = self::BASE_CAHCE_PATH;
        }

        if (!empty($this->cacheFolder)) {
            $fullCachePath .= $this->cacheFolder . DS;
        }
        if (!empty($subfolder)) {
            $fullCachePath .= $subfolder . DS;
        }

        $cacheName = "{$this->cacheFolder}_{$subfolder}_cache";

        $envConfig = new CacheEnvironmentConfigurations($cacheName, [
            'cache_path' => $fullCachePath,
            'default_lifetime' => 3600,
            'directory_permissions' => 0775,
        ]);

        $storage = new NativeCacheStorage(
            $envConfig,
            [],
            new DirectoryManager(),
            new FileContentManager(),
        );

        return new Cache($cacheName, $storage, [], $this->serializer);
    }

    public function getBaseCachePath(): string
    {
        return $this->baseCachePath . $this->cacheFolder . DS;
    }

    public function purgeAllCaches(): bool
    {
        $success = true;
        $folders = glob($this->getBaseCachePath() . '*', GLOB_ONLYDIR);

        foreach ($folders as $folder) {
            $cache = $this->createCache(basename($folder));
            $success = $success && $cache->clear();
        }

        return $success;
    }

    public function createInterface(string $cacheName, ?string $cacheFolder = null, ?LoggerInterface $logger = null): ?CacheInterface
    {
        $cacheInterface = $this->createCache($cacheFolder);
        if (class_exists($cacheName)) {
            if ($logger) {
                return new $cacheName($logger, $cacheInterface);
            } else {
                return new $cacheName($cacheInterface);
            }
        }
        return null;
    }
}