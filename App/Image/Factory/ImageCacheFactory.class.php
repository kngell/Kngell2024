<?php

declare(strict_types=1);

class ImageCacheFactory
{
    private const string PHYSICAL_PATH = ROOT_DIR . SCRIPT . DS . 'assets' . DS . 'img' . DS . 'cache' . DS;

    private array $instances = [];

    public function __construct(
        private SmartSerializerInterface $serializer,
        private FileOperationsInterface $fileOps,
        private FileMetadataService $service,
        private string $baseCachePath = STORAGE,
        private ?string $physicalPath = null,
    ) {
        if ($this->physicalPath === null) {
            $this->physicalPath = self::PHYSICAL_PATH;
        }
    }

    public function create(string $name = 'images'): ImageCache
    {
        if (isset($this->instances[$name])) {
            return $this->instances[$name];
        }

        $dedicateCache = new CacheManager(
            $this->serializer,
            $this->baseCachePath,
            'pages',
        )->createCache($name);

        $this->instances[$name] = new ImageCache(
            $dedicateCache,
            $this->fileOps,
            $this->service,
            $this->physicalPath,
        );

        return $this->instances[$name];
    }

    public function createFresh(string $name = 'images'): ImageCache
    {
        $dedicateCache = new CacheManager(
            $this->serializer,
            $this->baseCachePath,
            'cache',
        )->createCache($name);

        return new ImageCache(
            $dedicateCache,
            $this->fileOps,
            $this->service,
            $this->baseCachePath . $name . DS,
        );
    }

    public function getInstances(): array
    {
        return $this->instances;
    }

    public function clearInstance(string $name): void
    {
        unset($this->instances[$name]);
    }

    public function getBaseCachePath(): string
    {
        return $this->baseCachePath;
    }
}