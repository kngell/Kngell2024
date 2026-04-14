<?php

declare(strict_types=1);

class CacheEnvironmentConfigurations
{
    /** @var string|null */
    protected ?string $cacheIdentifier;

    /** @var string|null */
    protected ?string $fileCacheBasePath;

    /** @var int */
    protected int $maximumPathLength;

    /** @var int */
    protected int $defaultLifetime;

    /** @var array */
    protected array $cacheConfig;

    /**
     * Undocumented function.
     *
     * @param string|null $cacheIdentifier
     * @param array $cacheConfig
     */
    public function __construct(?string $cacheIdentifier, array $cacheConfig = [])
    {
        $this->cacheIdentifier = $cacheIdentifier;
        $this->cacheConfig = $cacheConfig;

        // Get configuration with defaults
        $defaults = [
            'cache_path' => CACHE_DIR . 'default' . DS,
            'cache_expires' => 3600,
            'cache_name' => 'system_cache',
            'use_cache' => true,
            'key' => 'auto',
            'default_driver' => 'native_storage',
        ];

        $config = array_merge($defaults, $cacheConfig);

        $this->fileCacheBasePath = rtrim($config['cache_path'], '/') . DS;
        $this->defaultLifetime = (int) ($config['cache_expires'] ?? 3600);
        $this->maximumPathLength = $this->defaultLifetime;
    }

    /**
     * The maximum length of filenames (including path) supported by this build
     * of PHP. Available since PHP.
     *
     * @return int
     */
    public function getMaximumPathLength(): int
    {
        return $this->maximumPathLength;
    }

    /**
     * Undocumented function.
     *
     * @return string
     */
    public function getFileCacheBasePath(): string
    {
        return $this->fileCacheBasePath;
    }

    /**
     * Undocumented function.
     *
     * @return string
     */
    public function getCacheIdentifier(): string
    {
        return $this->cacheIdentifier ?? 'default_cache';
    }

    /**
     * Get default cache lifetime (TTL) in seconds.
     *
     * @return int
     */
    public function getDefaultLifetime(): int
    {
        return $this->defaultLifetime;
    }

    /**
     * Get full cache configuration.
     *
     * @return array
     */
    public function getCacheConfig(): array
    {
        return $this->cacheConfig;
    }

    /**
     * Get cache directory with identifier.
     *
     * @return string
     */
    public function getCacheDirectory(): string
    {
        $basePath = $this->getFileCacheBasePath();
        $identifier = $this->getCacheIdentifier();

        // Create directory if it doesn't exist
        $cacheDir = $basePath . $identifier . '/';
        if (!is_dir($cacheDir)) {
            $oldUmask = umask(0);
            mkdir($cacheDir, 0775, true);
            umask($oldUmask);
        }

        return $cacheDir;
    }

    /**
     * Check if cache is enabled.
     *
     * @return bool
     */
    public function isCacheEnabled(): bool
    {
        return (bool) ($this->cacheConfig['use_cache'] ?? true);
    }

    /**
     * Get driver configuration.
     *
     * @return array
     */
    public function getDriverConfig(): array
    {
        return $this->cacheConfig['drivers'] ?? [];
    }

    /**
     * Get default driver.
     *
     * @return string
     */
    public function getDefaultDriver(): string
    {
        return $this->cacheConfig['default_driver'] ?? 'native_storage';
    }
}