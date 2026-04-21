<?php

declare(strict_types=1);

class Repository implements RepositoryInterface
{
    use BulkUpdateTrait;
    use ConditionSegmenterTrait;
    use SqlKeywordHandlerTrait;
    protected const array COLUMN_MAPS = [];

    public function __construct(
        protected EntityManagerInterface $em,
    ) {
    }

    public function create(): void
    {
        try {
            $this->em->createQueryBuilder()->insert()->build();
        } catch (Throwable $th) {
            throw $th;
        }
    }

    public function delete(array $conditions = []): void
    {
        try {
            $conditions = $this->conditions($conditions);
            $this->em->createQueryBuilder()->delete()->where($conditions)->build();
        } catch (Throwable $th) {
            throw $th;
        }
    }

    public function update(null|string|Closure $table = null, array $conditions = []): void
    {
        try {
            $conditions = $this->conditions($conditions);
            $this->em->createQueryBuilder()->update($table)->where($conditions)->build();
        } catch (Throwable $th) {
            throw $th;
        }
    }

    public function findByID(int|string $id): void
    {
        if ($this->isEmpty($id)) {
            try {
                $fieldId = $this->em->getEntityKeyField();
                $this->em->createQueryBuilder()->select()->where([$fieldId => $id])->build();
            } catch (Throwable $th) {
                throw $th;
            }
        }
    }

    /**
     * For backward compatibility, keep findByIds but deprecate it.
     *
     * @deprecated Use fetchIds() instead - name better reflects purpose
     */
    public function findByIds(array $conditions = [], ?int $limit = null, ?int $offset = null, ?string $keyField = null): void
    {
        $this->fetchIds($conditions, $limit, $offset, $keyField);
    }

    public function fetchIds(array $conditions = [], ?int $limit = null, ?int $offset = null, ?string $keyField = null): void
    {
        try {
            $keyField = $keyField ?? $this->em->getEntityKeyField();
            $columns = [$keyField];

            $queryBuilder = $this->em->createQueryBuilder()
                ->select($columns)
                ->where($conditions);

            if ($limit !== null) {
                $queryBuilder->limit($limit);
            }
            if ($offset !== null) {
                $queryBuilder->offset($offset);
            }

            $queryBuilder->build();
        } catch (Throwable $th) {
            throw new RepositoryException('Failed to fetch IDs: ' . $th->getMessage(), 0, $th);
        }
    }

    public function findOneBy(array $conditions = [], array $columns = []): void
    {
        try {
            $this->em->createQueryBuilder()->select($columns)->where($conditions)->build();
        } catch (Throwable $th) {
            throw $th;
        }
    }

    public function findAll(array $conditions = [], ?int $limit = null, ?int $offset = null, array $columns = []): void
    {
        try {
            $this->findBy($conditions, $limit, $offset, $columns);
        } catch (Throwable $th) {
            throw $th;
        }
    }

    public function findBy(array $conditions = [], ?int $limit = null, ?int $offset = null, array $columns = []): void
    {
        try {
            $queryBuilder = $this->em->createQueryBuilder();
            $select = $queryBuilder->select($columns);

            if (!empty($conditions)) {
                $select->where($conditions);
            }

            // Add pagination if provided
            if ($limit !== null) {
                $select->limit($limit);
            }
            if ($offset !== null) {
                $select->offset($offset);
            }

            $select->build();
            // $this->debugSql($queryBuilder);
        } catch (Throwable $th) {
            throw $th;
        }
    }

    public function showColumns(string $tableName): void
    {
        try {
            $this->em->createQueryBuilder()->raw("SHOW COLUMNS FROM $tableName")->build();
        } catch (Throwable $th) {
            throw $th;
        }
    }

    public function count(array $conditions): void
    {
        $count = $this->em->createQueryBuilder()->select('count(*) As totalRecords');
        $conditions = $this->applyGlobalScopes($conditions);
        if (!empty($conditions)) {
            $this->applyMixedConditions($count, $conditions);
        }
        $count->build();
    }

    protected function getAllColumns(): array
    {
        $allColumns = [];
        foreach (static::COLUMN_MAPS as $key => $columnMap) {
            foreach ($columnMap as $column) {
                $allColumns[] = $key . '.' . $column;
            }
        }
        return $allColumns;
    }

    protected function isArray(array $conditions): bool
    {
        if (!is_array($conditions)) {
            throw new RepositoryInvalidArgumentException('Argument Supplied is not an array');
        }

        return true;
    }

    protected function createDateRangeCondition(string $field, string $now = 'NOW()'): array
    {
        return [
            '(', $field, '<=', $now, 'OR', "$field IS NULL", ')',
        ];
    }

    protected function applyGlobalScopes(array $conditions): array
    {
        if (empty($conditions)) {
            return ['deleted_at IS NULL'];
        }

        return [
            '(', ...$conditions, ')',
            'AND', 'deleted_at IS NULL',
        ];
    }

    protected function debugSql(QueryBuilder $qb): void
    {
        (new DebugQuery())->debugSql($qb);
    }

    private function conditions(array $conditions = []): array
    {
        $entity = $this->em->getEntity();
        if ($entity instanceof Entity && empty($conditions)) {
            $fieldId = $this->em->getEntityKeyField();
            $value = $this->em->getEntityKeyValue();
            if ($value === null) {
                throw new RuntimeException(
                    'Cannot build conditions: entity key value is null. ' .
                    'Entity: ' . get_class($this->em->getEntity()) . ', ' .
                    'Key field: ' . $fieldId,
                );
            }

            return [$fieldId => $value];
        }
        return $conditions;
    }

    private function isEmpty(int|string $id): bool
    {
        if (empty($id)) {
            throw new RepositoryInvalidArgumentException('Argument shuold not be empty');
        }
        return true;
    }
}