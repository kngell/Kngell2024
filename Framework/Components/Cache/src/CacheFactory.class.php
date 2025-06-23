<?php

declare(strict_types=1);

class CacheFactory
{
    private ContainerInterface $container;
    private NativeCacheStorage $storage;

    public function __construct(private CacheEnvironmentConfigurations $cacheEnvConfig, ?ContainerInterface $container = null)
    {
        $this->container = $container ?? App::getInstance();
    }

    /**
     * Factory create method which create the cache object and instantiate the storage option
     * We also set a default storage options which is the NativeCacheStorage. So if the second
     * argument within the create method is left to null. Then the default cache storage object
     * will be created and all necessary argument injected within the constructor.
     *
     * @param string|null $cacheIdentifier
     * @param string|null $storage
     * @param array $options
     * @return CacheInterface
     */
    public function create(?string $cacheIdentifier = null, array $options = []): CacheInterface
    {
        $storageObject = $this->container->get(CacheStorageInterface::class, [
            'envConfigurations' => $this->cacheEnvConfig,
            'options' => $options,
        ]);
        if (! $storageObject instanceof CacheStorageInterface) {
            throw new cacheInvalidArgumentException('"' . $this->storage::class . '" is not a valid cache storage object.', 0);
        }
        $cacheObject = new Cache($cacheIdentifier, $storageObject, $options);
        if (! $cacheObject instanceof CacheInterface) {
            throw new cacheInvalidArgumentException('"' . $cacheObject::class . '" is not a valid cache storage object.', 0);
        }

        return $cacheObject;
    }
}