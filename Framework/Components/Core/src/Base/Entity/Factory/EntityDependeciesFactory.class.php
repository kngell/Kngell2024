<?php

declare(strict_types=1);

class EntityDependenciesFactory implements EntityDependenciesFactoryInterface
{
    private const CACHE_TTL = 3600;

    private array $instances = [];
    private ?EntityFactoryInterface $entityFactory = null;
    private ?TypePresenterFactoryInterface $typePresenterFactory = null;

    public function __construct(
        private TypeNormalizerInterface $normalizer,
        ?TypePresenterFactoryInterface $typePresenterFactory,
        private ?Closure $typePresenterFactoryCreator,
    ) {
        if ($typePresenterFactory !== null) {
            $this->typePresenterFactory = $typePresenterFactory;
        }
    }

    public function getTypePresenterFactory(): TypePresenterFactoryInterface
    {
        if ($this->typePresenterFactory === null) {
            // Try to create it using the creator closure
            if ($this->typePresenterFactoryCreator !== null) {
                try {
                    $this->typePresenterFactory = ($this->typePresenterFactoryCreator)();
                } catch (Throwable $e) {
                    throw new RuntimeException(
                        'Failed to create TypePresenterFactory: ' . $e->getMessage(),
                        0,
                        $e,
                    );
                }
            }

            if ($this->typePresenterFactory === null) {
                // If still null, create a stub factory
                $this->typePresenterFactory = $this->createStubTypePresenterFactory();
            }
        }

        return $this->typePresenterFactory;
    }

    public function getTransformer(): EntityToArrayTransformerInterface
    {
        return $this->instances[EntityToArrayTransformerInterface::class] ??=
            new EntityToArrayTransformer(
                $this->getTypePresenterFactory(),
                $this->normalizer,
            );
    }

    public function getMapper(): EntityMapperInterface
    {
        return $this->instances[EntityMapperInterface::class] ??= new EntityMapper();
    }

    public function getNormalizer(): TypeNormalizerInterface
    {
        return $this->normalizer;
    }

    public function getChangeTracker(): ChangeTrackerInterface
    {
        // $transformer = $this->getTransformer();
        // $serializer = new EntityCacheDataSerializer($this->getEntityFactory(), $transformer);
        // $keyGenerator = new EntityCacheKeyGenerator(
        //     $this->getNormalizer(),
        // );
        // $entityCache = new EntityCachingService(
        //     $this->createDefaultCache(),
        //     $serializer,
        //     $keyGenerator,
        // );
        return $this->instances[ChangeTrackerInterface::class] ??= new ChangeTracker();
    }

    public function getHydrator(): EntityHydratorInterface
    {
        return $this->instances[EntityHydratorInterface::class] ??=
            new EntityHydrator(
                $this->getNormalizer(),
                $this->getChangeTracker(),
                $this->getMapper(),
                $this->getEntityFactory(),
            );
    }

    public function getRelationManager(): EntityRelationManagerInterface
    {
        return $this->instances[EntityRelationManagerInterface::class] ??=
            new EntityRelationManager($this->getEntityFactory());
    }

    // public function getTypeHandlerFactory(): TypeHandlerFactory
    // {
    //     return $this->typeHandlerFactory;
    // }

    public function getEntityFactory(): EntityFactoryInterface
    {
        if ($this->entityFactory === null) {
            $this->entityFactory = new EntityFactory($this);
        }
        return $this->entityFactory;
    }

    public function setTypePresenterFactory(TypePresenterFactoryInterface $factory): void
    {
        $this->typePresenterFactory = $factory;

        // Recreate transformer if it already exists
        if (isset($this->instances[EntityToArrayTransformerInterface::class])) {
            unset($this->instances[EntityToArrayTransformerInterface::class]);
        }
    }

    public function setTypePresenterFactoryCreator(Closure $creator): void
    {
        $this->typePresenterFactoryCreator = $creator;
        $this->typePresenterFactory = null; // Reset so it will be created with new creator
    }

    private function createStubTypePresenterFactory(): TypePresenterFactoryInterface
    {
        // Create a simple factory that provides basic functionality
        return new class () implements TypePresenterFactoryInterface {
            private array $presenters = [];

            public function __construct()
            {
                $this->presenters['standard'] = new class () implements TypePresenterInterface {
                    public function supports(mixed $value, ?ReflectionProperty $property = null): bool
                    {
                        return true;
                    }

                    public function display(mixed $value, ?ReflectionProperty $property = null): mixed
                    {
                        return $value;
                    }
                };
            }

            public function getPresenterForValue(mixed $value, ?ReflectionProperty $property = null): TypePresenterInterface
            {
                return $this->presenters['standard'];
            }

            public function getPresenterForType(string $type): ?TypePresenterInterface
            {
                return $this->presenters['standard'];
            }

            public function displayValue(mixed $value, ?ReflectionProperty $property = null): mixed
            {
                return $value;
            }
        };
    }

    private function createDefaultCache(): CacheInterface
    {
        $envConfig = new CacheEnvironmentConfigurations('defaultCache', [
            'cache_path' => DS . 'storage' . DS . 'cache' . DS . 'tracker_cache' . DS,
            'default_lifetime' => self::CACHE_TTL,
        ]);

        $storage = new NativeCacheStorage($envConfig, [], new DirectoryManager(), new FileContentManager());
        return new Cache('currency_cache', $storage, []);
    }
}