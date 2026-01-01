<?php

declare(strict_types=1);

class CacheFactory
{
    public function __construct(private CacheEnvironmentConfigurations $envConfigurations, private DirectoryManager $directoryManager, private FileContentManager $fileContentManager)
    {
    }

    /**
     * Create cache instance based on configuration.
     */
    public function create(?string $cacheIdentifier = null, array $options = []): CacheInterface
    {
        // Use provided identifier or from configuration
        $identifier = $cacheIdentifier ?? $this->envConfigurations->getCacheIdentifier();

        // Get driver configuration
        $driverConfig = $this->envConfigurations->getDriverConfig();
        $defaultDriver = $this->envConfigurations->getDefaultDriver();

        // Get driver class
        $driverName = $options['driver'] ?? $defaultDriver;
        $driverClass = $driverConfig[$driverName]['class'] ?? 'NativeCacheStorage';

        // Create storage instance
        $storage = $this->createStorage($driverClass, $identifier, $options);

        // Create cache instance
        return new Cache($identifier, $storage, $options);
    }

    /**
     * Create storage instance.
     */
    private function createStorage(string $driverClass, string $identifier, array $options): CacheStorageInterface
    {
        // Ensure class exists
        if (!class_exists($driverClass)) {
            throw new CacheException("Cache driver class '{$driverClass}' not found.");
        }

        // Create environment configuration for this specific cache
        $envConfig = new CacheEnvironmentConfigurations($identifier, $this->envConfigurations->getCacheConfig());

        // Create storage instance
        return new $driverClass($envConfig, $options, $this->directoryManager, $this->fileContentManager);
    }

    /**
     * Create specialized cache instances.
     */
    public static function createCurrencyCache(): CacheInterface
    {
        $config = new CacheEnvironmentConfigurations('currency_cache', [
            'cache_path' => STORAGE . 'cache/currency/',
            'cache_expires' => 3600, // 1 hour
            'use_cache' => true,
            'default_driver' => 'native_storage',
        ]);

        $factory = new self($config, new DirectoryManager(), new FileContentManager());
        return $factory->create();
    }

    public static function createRegionCache(): CacheInterface
    {
        $config = new CacheEnvironmentConfigurations('region_cache', [
            'cache_path' => DS . 'storage' . DS . 'cache' . DS . 'region' . DS,
            'default_lifetime' => 7200, // 2 hours
            'use_cache' => true,
        ]);

        $factory = new self($config, new DirectoryManager(), new FileContentManager());
        return $factory->create();
    }

    public static function createLocaleCache(): CacheInterface
    {
        $config = new CacheEnvironmentConfigurations('locale_cache', [
            'cache_path' => DS . 'storage' . DS . 'cache' . DS . 'locale' . DS,
            'default_lifetime' => 86400, // 24 hours
        ]);

        $factory = new self($config, new DirectoryManager(), new FileContentManager());
        return $factory->create();
    }
}