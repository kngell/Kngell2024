<?php

declare(strict_types=1);

class EntityManager implements EntityManagerInterface
{
    private Entity|array|CollectionInterface $entity;
    private ReflectionObject $reflector;
    private array $repositories = [];
    private ?AbstractQueryBuilder $queryBuilder;
    private string $entityFieldId;
    private array $tableAlias = [];

    public function __construct(
        private DataMapperInterface $mapper,
        private TablesAliasHelper $tableAliasHelper,
        private EntityFactoryInterface $entityFactory,
    ) {
    }

    // ============ TRANSACTION METHODS ============

    public function beginTransaction(): bool
    {
        return $this->mapper->beginTransaction();
    }

    public function commit(): bool
    {
        return $this->mapper->commit();
    }

    public function rollback(): bool
    {
        return $this->mapper->rollback();
    }

    public function getConnection(): DatabaseConnectionInterface
    {
        return $this->mapper->getConnexion();
    }

    // ============ QUERY BUILDING ============

    public function createQueryBuilder(): QueryBuilder
    {
        return new QueryBuilder($this);
    }

    public function clearQuery(): self
    {
        $this->queryBuilder = null;
        return $this;
    }

    /**
     * @param null|AbstractQueryBuilder $queryBuilder
     *
     * @return EntityManager
     */
    public function setQueryBuilder(?AbstractQueryBuilder $queryBuilder): EntityManager
    {
        $this->queryBuilder = $queryBuilder;
        return $this;
    }

    // ============ REPOSITORY MANAGEMENT ============

    /**
     * Get repository for entity with proper type handling.
     */
    public function getRepository(Entity|CollectionInterface|string|null $entityName = null): RepositoryInterface
    {
        $entityClass = $this->resolveEntityClass($entityName);

        if (isset($this->repositories[$entityClass])) {
            return $this->repositories[$entityClass];
        }

        return $this->repositories[$entityClass] = $this->createRepository($entityClass);
    }

    // ============ ENTITY MANAGEMENT ============

    /**
     * Set entity with proper type validation.
     */
    public function setEntity(Entity|array|CollectionInterface $entity): self
    {
        $this->validateEntityType($entity);
        $this->entity = $entity;

        if ($entity instanceof Entity) {
            $this->reflector = new ReflectionObject($entity);
        } else {
            $items = $entity->all();
            if (!empty($items) && $items[0] instanceof Entity) {
                $this->reflector = new ReflectionObject($items[0]);
            }
        }

        return $this;
    }

    public function getEntity(): Entity|CollectionInterface
    {
        if (!isset($this->entity)) {
            throw new DataAccessLayerException('No entity has been set');
        }
        return $this->entity;
    }

    public function hasEntity(): bool
    {
        return isset($this->entity);
    }

    public function getLastOperationId(): null|int
    {
        if ($this->isEntityKeyInitialized()) {
            return $this->getEntityKeyValue();
        }
        return null;
    }

    /**
     * Assign data to current entity with proper type handling.
     */
    public function assign(array $data): self
    {
        if (!isset($this->entity)) {
            throw new DataAccessLayerException('No entity set for assignment');
        }

        if (ArrayUtils::isAssoc($data)) {
            $this->assignToSingleEntity($data);
        } else {
            $this->assignToCollection($data);
        }

        return $this;
    }

    public function getEntityKeyField(): string|bool
    {
        if (isset($this->entityFieldId)) {
            return $this->entityFieldId;
        }

        if ($this->entity instanceof Entity) {
            return $this->entity->getEntityKeyField();
        }

        // For collections, get key field from first item
        $items = $this->entity->all();
        if (!empty($items) && $items[0] instanceof Entity) {
            return $items[0]->getEntityKeyField();
        }

        return false;
    }

    /**
     * Get entity key value with proper reflection.
     */
    public function getEntityKeyValue(): mixed
    {
        $keyField = $this->getEntityKeyField();
        if (!$keyField) {
            return null;
        }

        if ($this->entity instanceof CollectionInterface) {
            throw new DataAccessLayerException('Cannot get key value from collection');
        }

        $propertyName = StringUtils::underscoreToStudlyCaps($keyField);
        $getter = 'get' . $propertyName;

        if ($this->reflector->hasMethod($getter) && $this->reflector->getMethod($getter)->isPublic()) {
            if ($this->entity->isInitialized($propertyName)) {
                return $this->entity->$getter();
            }
        }
        return null;
        // // Fallback to direct property access
        // try {
        //     if ($this->reflector->hasProperty($propertyName)) {
        //         $property = $this->reflector->getProperty($propertyName);
        //         return $property->getValue($this->entity);
        //     }
        //     return false;
        // } catch (ReflectionException $e) {
        //     throw new DataAccessLayerException(
        //         "Cannot access key field '{$keyField}' on entity " . $this->entity::class,
        //     );
        // }
    }

    public function isEntityKeyInitialized(): bool
    {
        if ($this->entity instanceof CollectionInterface) {
            return $this->isCollectionKeyInitialized();
        }

        $fieldId = $this->entityFieldId ?? $this->getEntityKeyField();
        if (!$fieldId) {
            return false;
        }
        try {
            $isInitialize = false;
            $keyValue = $this->getEntityKeyValue();
            if ($keyValue) {
                $isInitialize = true;
            }
            if (!$isInitialize) {
                $keyProperty = $this->entity->getEntityKeyProperty();
                if ($this->entity->hasProperty($keyProperty)) {
                    $isInitialize = $this->entity->isInitialized($keyProperty);
                }
            }

            return $isInitialize;
        } catch (ReflectionException $e) {
            return false;
        }
    }

    /**
     * Extract entity properties with proper type handling.
     */
    public function getEntityProperties(): array
    {
        if ($this->entity instanceof CollectionInterface) {
            return $this->getCollectionProperties();
        }

        return $this->getSingleEntityProperties();
    }

    // ============ PERSISTENCE & QUERY EXECUTION ============

    public function persist(): self
    {
        if (!$this->queryBuilder) {
            error_log('ERROR: EntityManager::persist() - No query builder set!');
            throw new RuntimeException('No query built to persist');
        }

        $sql = $this->queryBuilder->getQuery();
        $parameters = $this->queryBuilder->getParameters();
        // dump('Sql: ' . $sql, 'Parameters :' . print_r($parameters, true));
        $this->mapper->persist($sql, $parameters, false);
        return $this;
    }

    public function getQueryResult(): QueryResult
    {
        if (!$this->queryBuilder) {
            throw new RuntimeException('No query built to get results');
        }
        return new QueryResult(
            $this->mapper,
            $this->resolveEntityClass(),
            $this->queryBuilder->getTableAlias(),
            $this->entityFactory,
            $this->queryBuilder->getLogicalToPhysicalMap(),
        );
    }

    /**
     * Quick save helper for single entity.
     */
    public function save(Entity $entity): self
    {
        $this->setEntity($entity);
        $properties = $this->getEntityProperties();

        if ($this->isEntityKeyInitialized() && $this->getEntityKeyValue()) {
            // Update existing entity
            $keyField = $this->getEntityKeyField();
            $keyValue = $this->getEntityKeyValue();

            $this->createQueryBuilder()
                ->update($this->table())
                ->set($properties)
                ->where([$keyField => $keyValue])
                ->build();
        } else {
            // Insert new entity
            $this->createQueryBuilder()
                ->insert($this->table())
                ->columns(array_keys($properties))
                ->values(array_values($properties))
                ->build();
        }

        return $this->persist();
    }

    // ============ QUERY METHODS ============

    public function find(int|string $id): ?Entity
    {
        $keyField = $this->getEntityKeyField();
        if (!$keyField) {
            throw new DataAccessLayerException('Entity does not have a key field defined');
        }

        $this->createQueryBuilder()
            ->select()
            ->where([$keyField => $id])
            ->limit(1)
            ->build();

        return $this->persist()->getQueryResult()->setOperation('single')->asClass();
    }

    public function findAll(array $conditions = []): array
    {
        $queryBuilder = $this->createQueryBuilder()->select();

        if (!empty($conditions)) {
            $queryBuilder->where($conditions);
        }

        $queryBuilder->build();

        $result = $this->persist()->getQueryResult()->setOperation('all')->asClass();
        return $result ?? [];
    }

    // ============ UTILITY METHODS ============

    public function table(): string
    {
        if ($this->entity instanceof Entity) {
            return $this->entity->table();
        }

        // For collections, get table from first item
        $items = $this->entity->all();
        if (!empty($items) && $items[0] instanceof Entity) {
            return $items[0]->table();
        }

        throw new DataAccessLayerException('Cannot determine table name');
    }

    public function getEntityData(): array
    {
        if ($this->entity instanceof Entity) {
            return $this->getEntityProperties();
        }

        $data = [];
        foreach ($this->entity as $singleEntity) {
            $data[] = $singleEntity->toArray();
        }
        return $data;
    }

    public function getDirtyData(): array
    {
        if ($this->entity instanceof Entity) {
            return $this->entity->getDirtyData();
        }

        $data = [];
        foreach ($this->entity as $singleEntity) {
            $data[] = $singleEntity->getDirtyData();
        }
        return $data;
    }

    public function hasData(): bool
    {
        return !$this->isEmpty();
    }

    public function getTableAliasHelper(): TablesAliasHelper
    {
        return $this->tableAliasHelper;
    }

    public function getTableAlias(): array
    {
        return $this->tableAlias;
    }

    public function getNormalizer(): TypeNormalizerInterface
    {
        return $this->entityFactory->getNormalizer();
    }

    /**
     * Resolve entity class name from various input types.
     */
    private function resolveEntityClass(Entity|CollectionInterface|string|null $entityName = null): string
    {
        if ($entityName === null) {
            if (!isset($this->entity)) {
                throw new DataAccessLayerException('No entity set and no entity name provided');
            }
            return $this->entity instanceof Entity ? $this->entity::class : $this->getCollectionEntityClass();
        }

        if ($entityName instanceof Entity) {
            return $entityName::class;
        }
        if ($entityName instanceof CollectionInterface) {
            $name = $entityName->first();
            return $name::class;
        }

        if (!class_exists($entityName)) {
            throw new DataAccessLayerException("Entity class '{$entityName}' does not exist");
        }

        return $entityName;
    }

    /**
     * Get entity class from collection.
     */
    private function getCollectionEntityClass(): string
    {
        if (!$this->entity instanceof CollectionInterface) {
            throw new DataAccessLayerException('Current entity is not a collection');
        }

        $items = $this->entity->all();
        if (empty($items)) {
            throw new DataAccessLayerException('Cannot determine entity class from empty collection');
        }

        $firstItem = $items[0];
        if (!$firstItem instanceof Entity) {
            throw new DataAccessLayerException('Collection contains non-entity items');
        }

        return $firstItem::class;
    }

    /**
     * Create repository instance for entity class.
     */
    private function createRepository(string $entityClass): RepositoryInterface
    {
        $repositoryClass = $entityClass . 'Repository';

        if (class_exists($repositoryClass)) {
            return new $repositoryClass($this);
        }

        // Fallback to base repository
        return new Repository($this);
    }

    /**
     * Validate entity type.
     */
    private function validateEntityType(mixed $entity): void
    {
        if (!$entity instanceof Entity && !$entity instanceof CollectionInterface) {
            throw new InvalidArgumentException(
                'Entity must be an instance of Entity or CollectionInterface',
            );
        }

        if ($entity instanceof CollectionInterface) {
            $this->validateCollectionContents($entity);
        }
    }

    /**
     * Validate that collection contains only Entity instances.
     */
    private function validateCollectionContents(CollectionInterface $collection): void
    {
        foreach ($collection as $item) {
            if (!$item instanceof Entity) {
                throw new InvalidArgumentException(
                    'Collection must contain only Entity instances',
                );
            }
        }
    }

    /**
     * Assign data to single entity.
     */
    private function assignToSingleEntity(array $data): void
    {
        $this->entity->assign($data);
    }

    /**
     * Assign data to collection of entities.
     */
    private function assignToCollection(array $data): void
    {
        if (ArrayUtils::isSequential($data)) {
            $collection = new Collection();
            foreach ($data as $singleDataSet) {
                // $entity = clone $this->entity;
                $entity = $this->createNewEntityInstance();
                $entity->assign($singleDataSet);
                $collection->add($entity);
            }
            $this->entity = $collection;
        } else {
            // Single data set for collection - assign to all entities
            foreach ($this->entity as $entity) {
                $entity->assign($data);
            }
        }
    }

    /**
     * Create new entity instance for collection.
     */
    private function createNewEntityInstance(): Entity
    {
        if (!isset($this->reflector)) {
            throw new DataAccessLayerException('Cannot create entity instance: no reflector available');
        }
        return $this->reflector->newInstance($this->entityFactory, [], []);
    }

    /**
     * Check if any entity in collection has initialized key.
     */
    private function isCollectionKeyInitialized(): bool
    {
        foreach ($this->entity as $entity) {
            $tempReflector = new ReflectionObject($entity);
            $fieldId = $entity->getEntityKeyField();

            if ($fieldId) {
                // $propertyName = StringUtils::underscoreToStudlyCaps($fieldId);
                try {
                    $property = $tempReflector->getProperty($fieldId);

                    if ($property->isInitialized($entity)) {
                        return true;
                    }
                } catch (ReflectionException $e) {
                    continue;
                }
            }
        }
        return false;
    }

    private function getSingleEntityProperties(): array
    {
        $properties = [];
        $allProperties = $this->reflector->getProperties(
            ReflectionProperty::IS_PUBLIC |
            ReflectionProperty::IS_PRIVATE |
            ReflectionProperty::IS_PROTECTED,
        );

        foreach ($allProperties as $property) {
            if (!$property->isInitialized($this->entity)) {
                continue;
            }

            // Skip non-persisted properties
            $notPersistedAttr = $property->getAttributes(NotPersisted::class);
            if (!empty($notPersistedAttr)) {
                continue;
            }
            $fieldName = StringUtils::StudlyCapsToUnderscore($property->getName());
            $value = $property->getValue($this->entity);

            $properties[$fieldName] = $this->normalizePropertyValue($value, $fieldName);
        }

        return $properties;
    }

    private function getCollectionProperties(): array
    {
        $collectionProperties = [];

        foreach ($this->entity as $index => $entity) {
            $reflector = new ReflectionObject($entity);
            $properties = [];

            $allProperties = $reflector->getProperties(
                ReflectionProperty::IS_PUBLIC |
                ReflectionProperty::IS_PRIVATE |
                ReflectionProperty::IS_PROTECTED,
            );

            foreach ($allProperties as $property) {
                if (!$property->isInitialized($entity)) {
                    continue;
                }

                $notPersistedAttr = $property->getAttributes(NotPersisted::class);
                if (!empty($notPersistedAttr)) {
                    continue;
                }

                $fieldName = StringUtils::StudlyCapsToUnderscore($property->getName());
                $value = $property->getValue($entity);

                $properties[$fieldName] = $this->normalizePropertyValue($value, $fieldName);
            }

            $collectionProperties[$index] = $properties;
        }

        return $collectionProperties;
    }

    /**
     * Normalize property value for database storage.
     */
    private function normalizePropertyValue(mixed $value, string $fieldName): mixed
    {
        if ($value instanceof Entity) {
            // Handle nested entities - store foreign key
            $keyField = $value->getEntityKeyField();
            if ($keyField) {
                $nestedReflector = new ReflectionObject($value);
                $propertyName = StringUtils::underscoreToStudlyCaps($keyField);

                try {
                    $property = $nestedReflector->getProperty($propertyName);
                    return $property->getValue($value);
                } catch (ReflectionException $e) {
                    // If we can't access the key, return null
                    return null;
                }
            }
            return null;
        }

        if ($value instanceof DateTimeInterface) {
            return $value->format('Y-m-d H:i:s');
        }

        if (is_bool($value)) {
            return $value ? 1 : 0;
        }

        return $value;
    }

    private function isEmpty(): bool
    {
        $properties = $this->getEntityProperties();

        if ($this->entity instanceof CollectionInterface) {
            return empty($properties);
        }

        foreach ($properties as $value) {
            if ($value !== null && $value !== '') {
                return false;
            }
        }

        return true;
    }

    public static function create(): self
    {
        // This would need your static dependencies to be available
        // For now, keeping the original signature
        return new self(
            self::$mapper ?? throw new RuntimeException('Mapper not configured'),
            self::$tableAliasHelper ?? throw new RuntimeException('Table alias helper not configured'),
            self::$entityFactory ?? throw new RuntimeException('EntityFactory not configured'),
        );
    }
}