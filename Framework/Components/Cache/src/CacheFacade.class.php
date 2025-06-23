<?php

declare(strict_types=1);

class CacheFacade
{
    public function __construct()
    {
    }

    /**
     * Undocumented function.
     *
     * @param string|null $cacheIdentifier
     * @param string|null $storage
     * @param array $cacheConfig
     * @param array $options
     * @return CacheInterface
     */
    public function create(?string $cacheIdentifier = null, array $cacheConfig = [], array $options = []): CacheInterface
    {
        try {
            return App::getInstance()->get(CacheFactory::class, [
                'cacheEnvConfig' => App::getInstance()->get(CacheEnvironmentConfigurations::class, [
                    'cacheIdentifier' => $cacheIdentifier,
                    'cacheConfig' => $cacheConfig,
                ]),
            ])->create($cacheIdentifier, $options);
        } catch (CacheException $e) {
            throw $e->getMessage();
        }
    }
}