<?php

declare(strict_types=1);

abstract class Model
{
    protected string $entityClassName;
    protected Entity $entity;

    public function __construct(
        protected EntityManagerInterface $em,
        protected EntityFactoryInterface $factory,
        private ModelContextInterface $context,
    ) {
        $this->entityClassName = $this->resolveEntityClassName();
        $this->getEntity();
    }

    public function __call(string $method, array $arguments): mixed
    {
        try {
            return $this->context->execute($method, $this->em, $this->entity, $arguments[0] ?? []);
        } catch (InvalidArgumentException $e) {
            throw new BadMethodCallException("Method $method does not exist", 0, $e);
        }
    }

    public function all($params = []): QueryResult
    {
        return $this->context->execute('all', $this->em, $this->entity, $params);
    }

    public function one($params = []): QueryResult
    {
        return $this->context->execute('one', $this->em, $this->entity, $params);
    }

    public function find($id): QueryResult
    {
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

    public function page(int $page, int $perPage, array $conditions = []): QueryResult
    {
        $params = ['page' => $page, 'perPage' => $perPage, 'conditions' => $conditions];
        return $this->context->execute('page', $this->em, $this->entity, $params);
    }

    public function ids(int $page, int $perPage, array $conditions = []): QueryResult
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

    public function save(mixed $data = null): QueryResult
    {
        return $this->context->execute('save', $this->em, $this->entity, $data);
    }

    public function insert($entity = null): QueryResult
    {
        return $this->context->execute('insert', $this->em, $this->entity, $entity);
    }

    public function update($entityOrConditions = [], array $conditions = []): QueryResult
    {
        $params = ['entity' => $entityOrConditions, 'conditions' => $conditions];
        return $this->context->execute('update', $this->em, $this->entity, $params);
    }

    public function delete($params = []): QueryResult
    {
        return $this->context->execute('delete', $this->em, $this->entity, $params);
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
        $result = $this->one($criteria);

        if (!$result->isEmpty()) {
            return $result->first();
        }

        $entity = $this->createEntity(array_merge($criteria, $attributes));
        $this->save($entity);

        return $entity;
    }

    /**
     * @return EntityManagerInterface
     */
    public function getEntityManager(): EntityManagerInterface
    {
        return $this->em;
    }

    private function resolveEntityClassName(): string
    {
        $className = str_replace('Model', '', static::class);
        if (!is_subclass_of($className, Entity::class)) {
            throw new DataAccessLayerException('Could not resolve valid Entity class for model: ' . static::class);
        }
        return $className;
    }

    private function getEntity(): Entity
    {
        if (!isset($this->entity) || $this->entity->isEmpty()) {
            $this->entity = $this->factory->create(
                $this->entityClassName,
            );
        }
        $this->em->setEntity($this->entity);
        return $this->entity;
    }
}