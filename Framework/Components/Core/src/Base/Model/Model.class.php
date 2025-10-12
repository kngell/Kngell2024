<?php

declare(strict_types=1);

abstract class Model
{
    protected EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager->setEntity($this->entity());
    }

    /**
     * @param Entity|array|string $params
     * @return QueryResult<Entity>
     */
    public function all(Entity|array|string $params = []): QueryResult
    {
        [$entity, $processedConditions] = $this->conditions($params);
        $this->em->getRepository($entity)->findAll($processedConditions);
        return $this->em->persist()->getQueryResult()->setOperation('all');
    }

    /**
     * @param int|string $id
     * @return QueryResult<Entity>
     */
    public function find(int|string $id): QueryResult
    {
        $this->em->getRepository($this->entity())->findByID($id);
        return $this->em->persist()->getQueryResult();
    }

    /**
     * Get first record(s) with optional conditions.
     *
     * @param array $conditions Optional filtering conditions
     * @param int $limit Number of records to return (default: 1)
     * @return QueryResult<Entity>
     */
    public function first(array $conditions = [], int $limit = 1): QueryResult
    {
        [$entity, $processedConditions] = $this->conditions($conditions);

        $this->em->getRepository($entity)->findBy($processedConditions, $limit, 0);

        $queryResult = $this->em->persist()->getQueryResult()->setOperation('first');
        $queryResult->setLimit($limit);

        return $queryResult;
    }

    /**
     * Get last record(s) with optional conditions.
     *
     * @param array $conditions Optional filtering conditions
     * @param int $limit Number of records to return (default: 1)
     * @return QueryResult<Entity>
     */
    public function last(array $conditions = [], int $limit = 1): QueryResult
    {
        [$entity, $processedConditions] = $this->conditions($conditions);
        $this->em->getRepository($entity)->findAll($processedConditions);

        $queryResult = $this->em->persist()->getQueryResult()->setOperation('last');

        // Store that we want the last records
        $queryResult->setLastLimit($limit);

        return $queryResult;
    }

    /**
     * Get paginated results.
     *
     * @param int $page Page number (1-based)
     * @param int $perPage Records per page
     * @param array $conditions Optional filtering conditions
     * @return QueryResult<Entity>
     */
    public function page(int $page, int $perPage, array $conditions = []): QueryResult
    {
        [$entity, $processedConditions] = $this->conditions($conditions);

        $offset = ($page - 1) * $perPage;
        $this->em->getRepository($entity)->findBy($processedConditions, $perPage, $offset);

        return $this->em->persist()->getQueryResult()->setOperation('all');
    }

    /**
     * Get specific number of records.
     *
     * @param int $limit Number of records to return
     * @param array $conditions Optional filtering conditions
     * @return QueryResult<Entity>
     */
    public function get(int $limit, array $conditions = []): QueryResult
    {
        [$entity, $processedConditions] = $this->conditions($conditions);
        $this->em->getRepository($entity)->findBy($processedConditions, $limit, 0);

        return $this->em->persist()->getQueryResult()->setOperation('all');
    }

    public function delete(Entity|array|string|int $params = []): QueryResult
    {
        [$entity, $conditions] = $this->conditions($params);

        // Defensive: if for any reason $entity is not an Entity instance, fallback to entityManager's entity
        if (! $entity instanceof Entity) {
            $entity = $this->em->getEntity();
        }

        if ($entity instanceof SoftDeletableInterface) {
            // use trait helper
            $entity->softDelete();

            // keep timestamps consistent if supported
            if ($entity instanceof TimestampableInterface) {
                $entity->setUpdatedAt(new DateTimeImmutable());
            }

            // make sure entity manager uses this instance (so getEntityProperties() sees changes)
            $this->em->setEntity($entity);

            // updating the entity will call repository->update(...) and then persist()->getResults()
            return $this->update($entity);
        }

        // Hard delete
        $this->em->getRepository($entity)->delete($conditions);
        return $this->em->persist()->getQueryResult();
    }

    /**
     * @param array|Entity|null $data
     * @return QueryResult<Entity>
     */
    public function save(array|Entity|null $data = null): QueryResult
    {
        if (is_array($data)) {
            $this->em->assign($data);
            $entity = $this->em->getEntity();
        } elseif ($data instanceof Entity) {
            $entity = $data;
            $this->em->setEntity($entity);
        } else {
            throw new DataAccessLayerException('No Data to save!');
        }

        $entity->touchTimestamps();

        if ($this->em->isEntityKeyInitialized()) {
            return $this->update($entity);
        }

        return $this->insert($entity);
    }

    /**
     * @param Entity|null $entity
     * @return QueryResult<Entity>
     */
    public function insert(Entity|null $entity = null): QueryResult
    {
        $this->em->getRepository($entity)->create();
        return $this->em->persist()->getQueryResult();
    }

    /**
     * @param Entity|array|string $params
     * @return QueryResult<Entity>
     */
    public function update(Entity|SoftDeletableInterface|array|string $params = []): QueryResult
    {
        if ($params instanceof Entity) {
            $entity = $params;
            $conditions = []; // Entity already has ID
        } else {
            list($entity, $conditions) = $this->conditions($params);
        }

        // Call repository directly
        $this->em->getRepository($entity)->update($conditions);

        return $this->em->persist()->getQueryResult();
    }


    // /**
    //  * @param Entity|array|string $params
    //  * @return QueryResult<Entity>
    //  */
    // public function update(Entity|array|string $params = []): QueryResult
    // {
    //     $conditionResult = $this->conditions($params);
    //     $this->em->getRepository($conditionResult->entity)->update($conditionResult->conditions);
    //     return $this->em->persist()->getResults();
    // }

    public function getTableColumns(string $tableName): string
    {
        $result = $this->showColumns($tableName);
        $colums = array_column($result->all(), 'Field');
        return StringUtils::camelCase('$' . implode(', $', $colums) . ';');
    }

    private function showColumns(string|null $tableName = null): QueryResult
    {
        if ($tableName === null) {
            $tableName = strtolower($this->em->getEntity()::class);
        }
        $this->em->getRepository()->showColumns($tableName);
        return $this->em->persist()->getQueryResult();
    }

    /**
     * @param Entity|array|string|int $params
     * @return array{0: Entity, 1: array<string,mixed>}
     */
    private function conditions(Entity|array|string|int $params = []): array
    {
        // If caller passed an Entity instance, return it directly
        if ($params instanceof Entity) {
            return [$params, []];
        }

        // Fallback to the EM's current entity instance
        $entity = $this->em->getEntity();

        if (is_array($params)) {
            return [$entity, $params];
        }

        if (is_string($params) || is_int($params)) {
            return $this->idCondition($params);
        }

        return [$entity, []];
    }

    private function idCondition(string|int $id): array
    {
        $fieldId = $this->em->getEntityKeyField();
        $entity = $this->em->getEntity();

        if ($fieldId) {
            return [$entity, [$fieldId => $id]];
        }

        return [$entity, []];
    }


    // /**
    //  * @param Entity|array|string|int $params
    //  * @return array{0: Entity, 1: array<string, mixed>}
    //  */
    // private function conditions(Entity|array|string|int $params = []): array
    // {
    //     if ($params instanceof Entity) {
    //         return [$params, []];
    //     }

    //     $entity = $this->em->getEntity();
    //     if (is_array($params)) {
    //         return [$entity, $params];
    //     }

    //     if (is_string($params) || is_int($params)) {
    //         return $this->idCondition($params);
    //     }

    //     return [$entity, []];
    // }


    // private function conditions(Entity|array|string|int $params = []): array
    // {
    //     return match (true) {
    //         $params instanceof Entity => [$params, []],
    //         is_array($params) => [null, $params],
    //         is_string($params) || is_int($params) => $this->idCondition($params),
    //         default => [null, []]
    //     };
    // }




    // private function idCondition(string|int $id): array
    // {
    //     $fieldId = $this->em->getEntityKeyField();
    //     return $fieldId ? [null, [$fieldId => $id]] : [null, []];
    // }

    private function entity(): Entity
    {
        $entityName = str_replace('Model', '', $this::class);
        return App::diGet($entityName);
    }
}