<?php

declare(strict_types=1);

abstract class Model
{
    use CommonQueryMethodsTrait;
    protected const string DELETE_OPTION = 'deleteOption';
    protected const string DEFAULT_ENTITY = 'defaultEntity';

    protected string $entityClassName;
    protected Entity $entity;
    protected array $columns = [];
    private array $eventData = [];
    private ?string $currentOp = null;
    private ?DateTimeImmutable $archiveAt = null;
    protected static array $identityMap = [];

    public function __construct(
        protected EntityManagerInterface $em,
        protected EntityFactoryInterface $factory,
        private ModelContextInterface $context,
        private ModelUtilityInterface $utils,
    ) {
        $this->entityClassName = $this->resolveEntityClassName();
        $this->getEntity();
        $this->context->initialize($this, $this->utils);
    }

    public function __call(string $method, array $arguments): mixed
    {
        try {
            return $this->context->execute($method, $this->em, $this->entity, $arguments[0] ?? []);
        } catch (InvalidArgumentException $e) {
            throw new BadMethodCallException("Method $method does not exist", 0, $e);
        }
    }

    public function all(array $params = [], bool $withRelations = false): QueryResult
    {
        $result = $this->context->execute('all', $this->em, $this->entity, $params);

        if (!empty($this->columns)) {
            $params['columns'] = $this->columns;
        }

        if ($withRelations) {
            $result->setFetchStrategy(FetchStrategy::RELATIONSHIP_AWARE);
        }

        return $result;
    }

    public function columns(string ...$columns): self
    {
        $this->columns = $columns;
        return $this;
    }

    public function one(array $params = [], bool $withRelations = false): QueryResult
    {
        $result = $this->context->execute('one', $this->em, $this->entity, $params);

        if ($withRelations) {
            $result->setFetchStrategy(FetchStrategy::RELATIONSHIP_AWARE);
        }

        return $result;
    }

    public function find(string|int $id): QueryResult
    {
        if (isset(self::$identityMap[static::class][$id])) {
            return self::$identityMap[static::class][$id];
        }
        return $this->context->execute('find', $this->em, $this->entity, $id);
    }

    public function first(array $conditions = [], int $limit = 1): QueryResult
    {
        $params = ['conditions' => $conditions, 'limit' => $limit];
        return $this->context->execute('first', $this->em, $this->entity, $params);
    }

    public function last(array $conditions = [], int $limit = 1): QueryResult
    {
        $params = ['conditions' => $conditions, 'limit' => $limit];
        return $this->context->execute('last', $this->em, $this->entity, $params);
    }

    public function page(int $page, int $perPage, array $conditions = [], array $columns = []): QueryResult
    {
        $params = ['page' => $page, 'perPage' => $perPage, 'columns' => $columns, 'conditions' => $conditions];
        return $this->context->execute('page', $this->em, $this->entity, $params);
    }

    public function ids(null|int|string $page = null, ?int $perPage = null, array $conditions = []): QueryResult
    {
        $params = ['page' => $page, 'perPage' => $perPage, 'conditions' => $conditions];
        $keyField = $this->entity->getEntityKeyField();
        if ($keyField) {
            $params['keyField'] = $keyField;
        }
        return $this->context->execute('ids', $this->em, $this->entity, $params);
    }

    public function get(int $limit, array $conditions = []): QueryResult
    {
        $params = ['conditions' => $conditions, 'limit' => $limit];
        return $this->context->execute('get', $this->em, $this->entity, $params);
    }

    public function save(null|array|Entity $data = null, array $conditions = []): QueryResult
    {
        $payload = $this->utils->normalizeData($data, $this->entity);
        if ($payload->isCollection()) {
            return $this->syncCollection($payload, $conditions);
        }
        if ($payload->hasId()) {
            return $this->update($payload, $conditions);
        }
        return $this->insert($payload);
    }

    public function insert(mixed $data = null): QueryResult
    {
        try {
            return $this->context->execute('insert', $this->em, $this->entity, $data);
        } finally {
        }
    }

    public function update(mixed $data = null, array $conditions = []): ?QueryResult
    {
        try {
            $params = ['data' => $data, 'conditions' => $conditions];
            return $this->context->execute('update', $this->em, $this->entity, $params);
        } finally {
        }
    }

    public function delete(mixed $params = []): QueryResult
    {
        try {
            $entity = $this->entity;
            if (!empty($params[self::DEFAULT_ENTITY])) {
                $entity = $params[self::DEFAULT_ENTITY];
                unset($params[self::DEFAULT_ENTITY]);
            }
            if (is_int($params)) {
                $params = [$this->entity->getEntityKeyField(), $params];
            }
            return $this->context->execute('delete', $this->em, $entity, $params);
        } finally {
            if (is_string($params)) {
                $this->clearIdentityMap($params);
            } elseif (is_array($params) && isset($params['id'])) {
                $this->clearIdentityMap($params['id']);
            }
        }
    }

    public function createFromArray(array $data): Entity
    {
        return $this->entity->assign($data);
    }

    public function restore(mixed $params = []): QueryResult
    {
        return $this->context->execute('restore', $this->em, $this->entity, $params);
    }
    // ============ UTILITIES ============

    public function count(array $conditions = []): int
    {
        return $this->context->execute('count', $this->em, $this->entity, $conditions)->count();
    }

    public function exists(array $conditions = []): bool
    {
        return $this->count($conditions) > 0;
    }

    public function registerStrategy(string $name, ModelStrategyInterface $strategy): void
    {
        $this->context->register($name, $strategy);
    }

    public function findOrCreate(array $criteria, array $attributes = []): Entity
    {
        $cacheKey = $this->entityClassName . ':' . serialize($criteria);
        if (isset(self::$identityMap[$cacheKey])) {
            return self::$identityMap[$cacheKey];
        }

        $result = $this->one($criteria);

        if (!$result->isEmpty()) {
            /** @var Entity */
            $entity = $result->asClass();
            self::$identityMap[$cacheKey] = $entity;
            return $entity->completeHydration();
        }
        return $this->factory->createFromClient($this->entityClassName, $attributes);
    }

    public function deobsfuscated(string $field, mixed $value): ?int
    {
        return $this->entity->deobsfuscated($field, $value);
    }

    /**
     * @return EntityManagerInterface
     */
    public function getEntityManager(): EntityManagerInterface
    {
        return $this->em;
    }

    public function getEntityKeyfield(): ?string
    {
        if (is_object($this->entity)) {
            return $this->entity->getEntityKeyField();
        }
        return null;
    }

    public function addToIdentityMap(Entity|array|CollectionInterface $entity): void
    {
        if ($entity instanceof Entity) {
            $this->store($entity);
            return;
        }

        if ($entity instanceof CollectionInterface) {
            $entity = $entity->all();
        }

        if (!is_array($entity) || !ArrayUtils::isObjectList($entity)) {
            throw new InvalidArgumentException(
                'addToIdentityMap expects an Entity, a list of Entity, or a CollectionInterface',
            );
        }

        foreach ($entity as $singleEntity) {
            $this->store($singleEntity);
        }
    }

    public function getFromIdentityMap(mixed $id): ?Entity
    {
        return self::$identityMap[static::class][$id] ?? null;
    }

    public function currentOperation(): ?string
    {
        $currentOp = $this->currentOp;
        $this->currentOp = null;
        return $currentOp;
    }

    public function clearIdentityMap(mixed $id): void
    {
        if (isset(self::$identityMap[static::class][$id])) {
            unset(self::$identityMap[static::class][$id]);
        }
    }

    public function clearState(): void
    {
        $this->em->reset();
        if (isset(static::$identityMap[static::class])) {
            static::$identityMap[static::class] = [];
        }
    }

    /**
     * @return array
     */
    public function getEventData(): array
    {
        return $this->eventData;
    }

    public function getDeleteOptionKey(): string
    {
        return self::DELETE_OPTION;
    }

    public function getDefaultEntityKey(): string
    {
        return self::DEFAULT_ENTITY;
    }

    /**
     * @param null|string $currentOp
     *
     * @return Model
     */
    public function setCurrentOperation(?string $currentOp): Model
    {
        $this->currentOp = $currentOp;

        return $this;
    }

    /**
     * @return null|DateTimeImmutable
     */
    public function getArchiveAt(): ?DateTimeImmutable
    {
        $archiveAt = $this->archiveAt;
        $this->archiveAt = null;
        return $archiveAt;
    }

    /**
     * @param null|DateTimeImmutable $archiveAt
     *
     * @return Model
     */
    public function setArchiveAt(?DateTimeImmutable $archiveAt): Model
    {
        $this->archiveAt = $archiveAt;

        return $this;
    }

    public function getEntity(): Entity
    {
        if (!isset($this->entity) || $this->entity->isEmpty()) {
            $this->entity = $this->factory->create(
                $this->entityClassName,
            );
        }
        return $this->entity;
    }

    protected function saveEventData(array $normalizedData): void
    {
        $keyField = $this->entity->getEntityKeyField();
        $keyProperty = $this->entity->getEntityKeyProperty();

        $id = $normalizedData[$keyField] ?? $normalizedData[$keyProperty] ?? null;

        if (!empty($id)) {
            $this->saveEntitySnapshot($id);
        }
    }

    protected function syncCollection(ModelOperationPayload $payload, array $conditions = []): QueryResult
    {
        $results = [];

        if ($payload->hasIds()) {
            if (empty($conditions)) {
                $conditions = [$payload->getKeyField() => array_unique($payload->getIds())];
            }

            $results['update'] = $this->update($payload, $conditions);
        }

        if ($payload->hasInserts()) {
            $results['insert'] = $this->insert($payload);
        }
        if (empty($results)) {
            return $this->em->getQueryResult()->setSkipped(true, 'Nothing to sync');
        }
        /** @var QueryResult $finalResult */
        $finalResult = $this->em->getQueryResult();
        $totalRows = 0;
        $skipped = true;
        $reason = '';

        foreach ($results as $res) {
            if (!$res->isSuccess()) {
                return $res;
            }
            $totalRows += $res->count();
            $skipped = $skipped && $res->wasSkipped();
            $reason .= ' ' . $res->getSkipReason();
        }

        // Set the final state as the aggregate of all parts
        $finalResult->setRowCount($totalRows)->setOperation('sync')->setSkipped($skipped)->setSkipReason($reason);

        return $finalResult;
    }

    private function saveEntitySnapshot(mixed $id): void
    {
        $keyField = $this->entity->getEntityKeyField();
        $entity = $this->getById($id, $keyField)?->asClass();

        if ($entity) {
            $this->eventData['old_entity_snapshot'] = clone $entity;
            $this->addToIdentityMap($entity);
        }
    }

    private function store(Entity $entity): void
    {
        $id = $entity->getEntityPrimarykeyValue();
        if ($id) {
            self::$identityMap[static::class][$id] = $entity;
        }
    }

    private function resolveEntityClassName(): string
    {
        $className = str_replace('Model', '', static::class);
        if (!is_subclass_of($className, Entity::class)) {
            throw new DataAccessLayerException('Could not resolve valid Entity class for model: ' . static::class);
        }
        return $className;
    }
}