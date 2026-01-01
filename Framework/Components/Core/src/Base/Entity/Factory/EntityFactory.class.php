<?php

declare(strict_types=1);

class EntityFactory implements EntityFactoryInterface
{
    public function __construct(
        private EntityDependenciesFactoryInterface $dependencies,
    ) {
    }

    public function create(
        string $entityClass,
        array $tableAlias = [],
        array $tableMap = [],
    ): Entity {
        if (!is_subclass_of($entityClass, Entity::class)) {
            throw new InvalidArgumentException(
                sprintf('Class "%s" must extend Entity', $entityClass),
            );
        }

        return new $entityClass(
            dependencies: $this->dependencies,
            tableAlias: $tableAlias,
            tableMap: $tableMap,
        );
    }

    public function createWithCustomServices(
        string $entityClass,
        ?EntityMapperInterface $mapper = null,
        ?EntityHydratorInterface $hydrator = null,
        ?EntityRelationManagerInterface $relationManager = null,
        array $tableAlias = [],
        array $tableMap = [],
    ): Entity {
        // For custom services, create a custom dependencies wrapper
        $customDependencies = new CustomEntityDependencies(
            $this->dependencies,
            $mapper,
            $hydrator,
            $relationManager,
        );

        return new $entityClass(
            dependencies: $customDependencies,
            tableAlias: $tableAlias,
            tableMap: $tableMap,
        );
    }

    public function createFromDatabase(
        string $entityClass,
        array $data,
        array $tableAlias = [],
        array $tableMap = [],
    ): Entity {
        $entity = $this->create($entityClass, $tableAlias, $tableMap);
        $entity->pdoHydrate($data);
        return $entity;
    }

    public function createFromClient(
        string $entityClass,
        array $data,
        array $tableAlias = [],
        array $tableMap = [],
    ): Entity {
        $entity = $this->create($entityClass, $tableAlias, $tableMap);
        $entity->assign($data);
        return $entity;
    }

    public function createMany(
        string $entityClass,
        array $dataSets,
        array $tableAlias = [],
        array $tableMap = [],
    ): array {
        $entities = [];
        foreach ($dataSets as $data) {
            $entities[] = $this->createFromDatabase($entityClass, $data, $tableAlias, $tableMap);
        }
        return $entities;
    }

    public function createWithRelationships(
        string $entityClass,
        array $data,
        array $relationships,
        array $tableAlias = [],
        array $tableMap = [],
    ): Entity {
        $entity = $this->create($entityClass, $tableAlias, $tableMap);
        $entity->pdoHydrate($data);

        foreach ($relationships as $relationName => $relationData) {
            $entity->__set($relationName . '.', $relationData);
        }

        $entity->completeHydration();
        return $entity;
    }

    public function getPrimaryKeyField(string $entityClass): string
    {
        // Static cache to avoid repeating reflection for every row in a loop
        static $pkCache = [];
        if (isset($pkCache[$entityClass])) {
            return $pkCache[$entityClass];
        }

        $reflection = new ReflectionClass($entityClass);

        foreach ($reflection->getProperties() as $property) {
            $attributes = $property->getAttributes(EntityFieldId::class);
            if (!empty($attributes)) {
                /** @var EntityFieldId $instance */
                $instance = $attributes[0]->newInstance();

                $pkName = $instance->getName();
                if ($pkName) {
                    return $pkCache[$entityClass] = $pkName;
                }
            }
        }

        return $pkCache[$entityClass] = 'id';
    }

    // Getters for backward compatibility
    public function getMapper(): EntityMapperInterface
    {
        return $this->dependencies->getMapper();
    }

    public function getNormalizer(): TypeNormalizerInterface
    {
        return $this->dependencies->getNormalizer();
    }

    public function getChangeTracker(): ChangeTrackerInterface
    {
        return $this->dependencies->getChangeTracker();
    }

    public function getHydrator(): EntityHydratorInterface
    {
        return $this->dependencies->getHydrator();
    }

    public function getRelationManager(): EntityRelationManagerInterface
    {
        return $this->dependencies->getRelationManager();
    }

    public function getTransformer(): EntityToArrayTransformerInterface
    {
        return $this->dependencies->getTransformer();
    }

    /**
     * @return EntityDependenciesFactoryInterface
     */
    public function getDependencies(): EntityDependenciesFactoryInterface
    {
        return $this->dependencies;
    }
}