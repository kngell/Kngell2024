<?php

declare(strict_types=1);

class EntityFactory implements EntityFactoryInterface
{
    private array $entityRelationshipsCache = [];

    public function __construct(
        private EntityDependenciesFactoryInterface $dependencies,
        private EntityIdentityMap $identityMap,
    ) {
    }

    public function create(
        string $entityClass,
        array $tableAlias = [],
        array $tableMap = [],
        mixed $id = null,
    ): Entity {
        if (!is_subclass_of($entityClass, Entity::class)) {
            throw new InvalidArgumentException(
                sprintf('Class "%s" must extend Entity', $entityClass),
            );
        }
        if ($id !== null) {
            $existing = $this->identityMap->get($entityClass, $id);
            if ($existing) {
                return $existing;
            }
        }
        $entity = new $entityClass(
            dependencies: $this->dependencies,
            tableAlias: $tableAlias,
            tableMap: $tableMap,
        );

        if ($id !== null) {
            $this->identityMap->set($entityClass, $id, $entity);
        }

        return $entity;
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
        static $pkCache = [];
        if (isset($pkCache[$entityClass])) {
            return $pkCache[$entityClass];
        }

        $reflection = CustomReflection::getInstance($entityClass)->getClass();
        foreach ($reflection->getProperties() as $property) {
            $attributes = $property->getAttributes(EntityFieldId::class);
            if (!empty($attributes)) {
                $instance = $attributes[0]->newInstance();
                return $pkCache[$entityClass] = $instance->getName() ?? $property->getName();
            }
        }
        return $pkCache[$entityClass] = 'id';
    }

    public function hasRelationships(string $entityClass): bool
    {
        try {
            $reflection = CustomReflection::getInstance($entityClass)->getClass();
            if (!$reflection->hasConstant('RELATIONSHIPS')) {
                return false;
            }

            $relationships = $reflection->getConstant('RELATIONSHIPS');
            return !empty($relationships);
        } catch (ReflectionException $e) {
            return false;
        }
    }

    public function getRelationships(string $entityClass): array
    {
        try {
            $reflection = CustomReflection::getInstance($entityClass)->getClass();
            if (!$reflection->hasConstant('RELATIONSHIPS')) {
                return [];
            }
            if (isset($this->entityRelationshipsCache[$entityClass])) {
                return $this->entityRelationshipsCache[$entityClass];
            }
            $this->entityRelationshipsCache[$entityClass] = $reflection->getConstant('RELATIONSHIPS');
            return $this->entityRelationshipsCache[$entityClass];
        } catch (ReflectionException $e) {
            return [];
        }
    }

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