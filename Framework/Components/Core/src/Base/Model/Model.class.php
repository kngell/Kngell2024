<?php

declare(strict_types=1);

abstract class Model
{
    protected EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager->setEntity($this->entity());
    }

    public function all(Entity|array|string $params = []) : QueryResult
    {
        list($entity, $conditions) = $this->conditions($params);
        $this->entityManager->getRepository($entity)->findAll($conditions);
        return $this->entityManager->persist()->getResults();
    }

    public function find(int|string $id) : QueryResult
    {
        $this->entityManager->getRepository()->findByID($id);
        return $this->entityManager->persist()->getResults();
    }

    public function delete(Entity|array|string|int $params = []) : QueryResult
    {
        list($entity, $conditions) = $this->conditions($params);
        $this->entityManager->getRepository($entity)->delete($conditions);
        return $this->entityManager->persist()->getResults();
    }

    public function save(array|Entity|null $data = null): QueryResult
    {
        if (is_array($data)) {
            $this->entityManager->assign($data);
        }
        if ($this->entityManager->isEntityKeyInitialized()) {
            return $this->update();
        }
        return $this->insert();
    }

    /**
     * @param array|Entity|null $data
     * @return QueryResult
     */
    public function insert(Entity|null $entity = null) : QueryResult
    {
        $this->entityManager->getrepository($entity)->create();
        return $this->entityManager->persist()->getResults();
    }

    public function update(Entity|array|string $params = []) : QueryResult
    {
        list($entity, $conditions) = $this->conditions($params);
        $this->entityManager->getrepository($entity)->update($conditions);
        return $this->entityManager->persist()->getResults();
    }

    public function showColumns(string|null $tableName = null) : QueryResult
    {
        if ($tableName === null) {
            $tableName = strtolower($this->entityManager->getEntity()::class);
        }
        $this->entityManager->getRepository()->showColumns($tableName);
        return $this->entityManager->persist()->getResults();
    }

    public function getTableColumns(string $tableName) : string
    {
        $result = $this->showColumns($tableName);
        $colums = array_column($result->all(), 'Field');
        return StringUtils::camelCase('$' . implode('; $', $colums));
    }

    private function conditions(Entity|array|string|int $params = []) : array
    {
        return match (true) {
            $params instanceof Entity => [$params, []],
            is_array($params) => [null, $params],
            is_string($params) || is_int($params) => $this->idCondition($params),
            default => [null, []]
        };
    }

    private function idCondition(string|int $id) : array
    {
        $fieldId = $this->entityManager->getEntityKeyField();
        return $fieldId ? [null, [$fieldId => $id]] : [null, []];
    }

    private function entity() : Entity
    {
        $entityName = str_replace('Model', '', $this::class);
        return App::diGet($entityName);
    }
}