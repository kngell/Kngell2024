<?php

declare(strict_types=1);

class CacheConfig
{
    /** @var string */
    private const DEFAULT_DRIVER = 'native_storage';

    /**
     * Main session configuration default array settings.
     *
     * @return array
     */
    public function baseConfiguration(): array
    {
        return [
            'use_cache' => true,
            'key' => 'auto',
            'cache_path' => '/Storage/Cache/',
            'cache_expires' => 3600,
            'default_storage' => self::DEFAULT_DRIVER,
            'drivers' => [
                'native_storage' => [
                    'class' => 'NativeCacheStorage',
                    'default' => true,
                ],
                'array_storage' => [
                    'class' => 'ArrayCacheStorage',
                    'default' => false,

                ],
                'pdo_storage' => [
                    'class' => 'PdoCacheStorage',
                    'default' => false,

                ],
            ],
        ];
    }
}
