<?php

declare(strict_types=1);

class EntityManagerOLD implements EntityManagerInterface
{
    private Entity|CollectionInterface $entity;
    private ReflectionObject $reflector;
    private $repositories = [];
    private ?AbstractQueryBuilder $queryBuilder;
    private string $entityFieldId;
    private array $tableAlias = [];

    public function __construct(
        private DataMapperInterface $mapper,
        private TablesAliasHelper $tableAliasHelper,
        private TypeNormalizerInterface $normalizer,
        private ChangeTrackerInterface $changeTracker,
    ) {
    }

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

    public function createQueryBuilder(): QueryBuilder
    {
        return new QueryBuilder($this);
    }

    /**
     * Improved repository factory with better error handling.
     */
    public function getRepository(Entity|string|null $entityName = null): RepositoryInterface|ProductRegionalPriceRepositoryInterface
    {
        // If no entity name provided, return base repository
        if ($entityName === null) {
            return new Repository($this);
        }

        // Extract class name from entity instance
        if ($entityName instanceof Entity) {
            $entityName = $entityName::class;
            !isset($this->entity) && !is_object($this->entity) ? new $entityName() : '';
        }

        // Check if repository already exists
        if (isset($this->repositories[$entityName])) {
            return $this->repositories[$entityName];
        }

        // Try to find repository class
        $repositoryClassName = $entityName . 'Repository';

        if (!class_exists($repositoryClassName)) {
            // Fallback to base repository if specific one doesn't exist
            $this->repositories[$entityName] = new Repository($this);
        } else {
            // Create specific repository
            $this->repositories[$entityName] = new $repositoryClassName($this);
        }

        return $this->repositories[$entityName];
    }

    public function persist(): self
    {
        if (!$this->queryBuilder) {
            throw new RuntimeException('No query built to persist');
        }

        $sql = $this->queryBuilder->getQuery();
        $parameters = $this->queryBuilder->getParameters();
        $bindArray = $this->queryBuilder->getBindArray();
        $this->mapper->persist($sql, $parameters, false);
        return $this;
    }

    /**
     * Helper method for quick entity saving.
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
                ->update()
                ->set($properties)
                ->where([$keyField => $keyValue])
                ->build();
        } else {
            // Insert new entity
            $this->createQueryBuilder()
                ->insert($this->table())
                ->columns((string) array_keys($properties))
                ->values(array_values($properties))
                ->build();
        }

        return $this->persist();
    }

    public function getQueryResult(): QueryResult
    {
        return new QueryResult(
            $this->mapper,
            $this->entity,
            $this->queryBuilder->getTableAlias(),
            $this->changeTracker,
            $this->normalizer,
            $this->queryBuilder->getLogicalToPhysicalMap(),
        );
    }

    public function assign(array $data): self
    {
        if (ArrayUtils::isSequential($data)) {
            $collection = new Collection();
            foreach ($data as $singledataSet) {
                $entity = clone $this->entity;
                $entity->assign($singledataSet);
                $collection->add($entity);
            }
            $this->entity = $collection;
        } else {
            $this->entity->assign($data);
        }

        return $this;
    }

    public function getEntityKeyField(): string|bool
    {
        if (isset($this->entityFieldId)) {
            return $this->entityFieldId;
        }
        return $this->entity->getEntityKeyField();
    }

    /**
     * Improved entity key value retrieval.
     */
    public function getEntityKeyValue(): mixed
    {
        $keyField = $this->getEntityKeyField();
        if (!$keyField) {
            return null;
        }

        // Convert to property name format
        $propertyName = StringUtils::underscoreToStudlyCaps($keyField);
        $getter = 'get' . $propertyName;

        if ($this->reflector->hasMethod($getter)) {
            return $this->entity->$getter();
        }

        // Fallback to direct property access
        $property = $this->reflector->getProperty($propertyName);
        return $property->getValue($this->entity);
    }

    public function find(int|string $id): ?Entity
    {
        $keyField = $this->getEntityKeyField();

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

    public function isEntityKeyInitialized(): bool
    {
        $fieldId = $this->entityFieldId ?? $this->getEntityKeyField();
        $properties = $this->reflector->getProperties(ReflectionProperty::IS_PRIVATE);
        foreach ($properties as $property) {
            $prop = StringUtils::StudlyCapsToUnderscore($property->getName());
            if ($prop === $fieldId && $property->isInitialized($this->entity)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Clear query expression (useful for reusing EntityManager).
     */
    public function clearQuery(): self
    {
        $this->queryBuilder = null;
        return $this;
    }

    public function hasEntity(): bool
    {
        return isset($this->entity);
    }

    /**
     * Improved entity properties extraction.
     */
    public function getEntityProperties(): array
    {
        $properties = [];
        $allProperties = $this->reflector->getProperties(
            ReflectionProperty::IS_PUBLIC |
            ReflectionProperty::IS_PRIVATE |
            ReflectionProperty::IS_PROTECTED,
        );

        foreach ($allProperties as $property) {
            // Skip properties that aren't initialized
            if (!$property->isInitialized($this->entity)) {
                continue;
            }
            $notPersistedAttr = $property->getAttributes(NotPersisted::class);
            if (!empty($notPersistedAttr)) {
                continue;
            }
            $fieldName = StringUtils::StudlyCapsToUnderscore($property->getName());
            $value = $property->getValue($this->entity);

            // Handle different value types
            if ($value instanceof Entity) {
                // Handle nested entities if needed
                $keyField = $value->getEntityKeyField();
                if ($keyField) {
                    $properties[$fieldName . '_id'] = $value->getFieldValue($keyField);
                }
            } else {
                $properties[$fieldName] = $value;
            }
        }

        return $properties;
    }

    public function hasData(): bool
    {
        return !$this->isEmpty();
    }

    public function getEntityData(): array
    {
        if ($this->entity instanceof Entity) {
            return $this->getEntityProperties();
        }
        $data = [];
        /** @var Entity $singleEntity */
        foreach ($this->entity as $singleEntity) {
            $data[] = $singleEntity->toArray();
        }
        return $data;
    }

    /**
     * Set the value of entity.
     *
     * @param Entity $entity
     *
     * @return self
     */
    public function setEntity(Entity $entity): self
    {
        $this->entity = $entity;
        $this->reflector = new ReflectionObject($this->entity);
        return $this;
    }

    public function table(): string
    {
        return $this->entity->table();
    }

    /**
     * Get the value of tableAliasHelper.
     *
     * @return TablesAliasHelper
     */
    public function getTableAliasHelper(): TablesAliasHelper
    {
        return $this->tableAliasHelper;
    }

    /**
     * Get the value of entity.
     *
     * @return Entity
     */
    public function getEntity(): Entity
    {
        return $this->entity;
    }

    /**
     * @return array
     */
    public function getTableAlias(): array
    {
        return $this->tableAlias;
    }

    /**
     * @return TypeNormalizerInterface
     */
    public function getNormalizer(): TypeNormalizerInterface
    {
        return $this->normalizer;
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

    private function isEmpty(): bool
    {
        $properties = $this->getEntityProperties();

        foreach ($properties as $value) {
            if ($value !== null && $value !== '') {
                return false;
            }
        }

        return true;
    }

    public static function create(): self
    {
        return new self(self::$mapper, self::$tableAliasHelper, self::$normalizer, self::$changeTracker);
    }
}