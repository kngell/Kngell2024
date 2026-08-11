<?php

declare(strict_types=1);

use Psr\Log\LoggerInterface;

class Repository implements RepositoryInterface
{
    use BulkUpdateTrait;
    use ConditionSegmenterTrait;
    use SqlKeywordHandlerTrait;
    protected const array COLUMN_MAPS = [];

    protected ?CustomLogger $logger = null;

    public function __construct(
        protected EntityManagerInterface $em,
        LoggerInterface $logger,
    ) {
        $this->logger = $logger;
    }

    public function setLogger(CustomLogger $logger): void
    {
        $this->logger = $logger;
    }

    public function create(): void
    {
        try {
            $qb = $this->em->createQueryBuilder();
            $qb->insert()->build();
            // $this->debugSql($qb);
        } catch (Throwable $th) {
            throw $th;
        }
    }

    public function delete(array $conditions = []): void
    {
        $conditions = $this->conditions($conditions);
        $this->em->createQueryBuilder()->delete()->where($conditions)->build();
    }

    public function update(null|string|Closure $table = null, array $conditions = []): void
    {
        $conditions = $this->conditions($conditions);
        $conditions = $this->guardSoftDeleteUpdate($conditions);
        $qb = $this->em->createQueryBuilder();
        $update = $qb->update($table);

        if (!empty($conditions)) {
            $this->applyMixedConditions($update, $conditions);
        }

        $update->build();
        // $this->debugSql($qb, 'json');
    }

    public function conditionalUpdate(null|string|Closure $table = null, array|entity|CollectionInterface $data = [], array $conditions = []): void
    {
        if (empty($conditions)) {
            return;
        }
        $conditions = $this->guardSoftDeleteUpdate($conditions);
        $update = $this->em->createQueryBuilder()->update($table)->set($data);

        if (!empty($conditions)) {
            $this->applyMixedConditions($update, $conditions);
        }

        $update->build();
    }

    public function findByID(int|string $id): void
    {
        if ($this->isValid($id)) {
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

            $qb = $this->em->createQueryBuilder();
            $queryBuilder = $qb
                ->select($columns)
                ->where($conditions);

            if ($limit !== null) {
                $queryBuilder->limit($limit);
            }
            if ($offset !== null) {
                $queryBuilder->offset($offset);
            }

            $queryBuilder->build();
            // $this->debugSql($qb);
        } catch (Throwable $th) {
            throw new RepositoryException('Failed to fetch IDs: ' . $th->getMessage(), 0, $th);
        }
    }

    public function findOneBy(array $conditions = [], array $columns = []): void
    {
        try {
            if (isset($conditions['ORDER BY'])) {
                $sort['ORDER BY'] = $conditions['ORDER BY'] ?? null;
                unset($conditions['ORDER BY']);
            }

            $qb = $this->em->createQueryBuilder();
            $select = $qb->select($columns)->where($conditions);
            if (!empty($sort)) {
                $this->applySqlKeywords($sort, $select);
            }
            $select->build();
            // $this->debugSql($qb);
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

            $sort['ORDER BY'] = $conditions['ORDER BY'] ?? null;
            unset($conditions['ORDER BY']);

            if (!empty($conditions)) {
                $this->applyMixedConditions($select, $conditions);
            }
            if ($sort !== null) {
                $this->applySqlKeywords($sort, $select);
            }
            if ($limit !== null) {
                $select->limit($limit);
            }
            if ($offset !== null) {
                $select->offset($offset);
            }

            $select->build();
            // $this->debugSql($queryBuilder, 'json');
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
        $qb = $this->em->createQueryBuilder();
        $count = $qb->select('count(*) As totalRecords');
        if (!empty($conditions)) {
            $this->applyMixedConditions($count, $conditions);
        }
        $count->build();
        // $this->debugSql($qb);
    }

    protected function buildModeConditions(array $conditions, ?string $mode): array
    {
        switch ($mode) {
            case ConditionListMode::MODE_ADMIN->value:
                return $this->applyGlobalScopes($conditions);
            case ConditionListMode::MODE_RESTORABLE->value:
                return array_merge(['deleted_at' => 'is not null'], $conditions);
            case ConditionListMode::MODE_FRONTEND->value:
                $conditions = array_merge(
                    ['is_active' => true],
                    $this->createDateRangeCondition('valid_from', 'NOW()', '<='),
                    $this->createDateRangeCondition('valid_to', 'NOW()', '>='),
                    $conditions,
                );
                return $this->applyGlobalScopes($conditions);
            default:
                return $conditions;
        }
    }

    protected function extractCaseConditions(array &$conditions): array
    {
        $caseConditions = [];
        if (isset($conditions[SpecialConditions::CASE->value])) {
            $caseConditions = $conditions[SpecialConditions::CASE->value];
            unset($conditions[SpecialConditions::CASE->value]);
        }
        return $caseConditions;
    }

    protected function extractModeFromConditions(array &$conditions): ?string
    {
        foreach (ConditionListMode::cases() as $mode) {
            if (isset($conditions[$mode->value])) {
                unset($conditions[$mode->value]);
                return $mode->value;
            }
        }
        return null;
    }

    protected function removeModeFlags(array $conditions): array
    {
        foreach (ConditionListMode::cases() as $mode) {
            unset($conditions[$mode->value]);
        }
        return $conditions;
    }

    protected function extractLimitOffset(array $conditions): array
    {
        $limit = $conditions['limit'] ?? $conditions['LIMIT'] ?? null;
        $offset = $conditions['offset'] ?? $conditions['OFFSET'] ?? null;

        unset($conditions['limit'], $conditions['LIMIT'], $conditions['offset'], $conditions['OFFSET']);

        return [
            $conditions,
            $limit !== null ? (int) $limit : null,
            $offset !== null ? (int) $offset : null,
        ];
    }

    protected function hasLimitOrOffset(array $conditions): bool
    {
        return isset($conditions['limit']) || isset($conditions['LIMIT']) ||
               isset($conditions['offset']) || isset($conditions['OFFSET']);
    }

    protected function getAllColumns(?string $table = null): array
    {
        $allColumns = [];
        $columns = static::COLUMN_MAPS;

        if ($table !== null && isset(static::COLUMN_MAPS[$table])) {
            $columns = [$table => static::COLUMN_MAPS[$table]];
        }

        foreach ($columns as $key => $columnMap) {
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

    protected function createDateRangeCondition(string $field, string $now = 'NOW()', string $operator = '<='): array
    {
        return [
            '(', $field, $operator, $now, 'OR', "$field IS NULL", ')',
        ];
    }

    protected function applyGlobalScopes(array $conditions): array
    {
        if ($this->resolveSoftDeletableEntity() === null) {
            return $conditions;
        }

        if ($this->conditionsTouchDeletedAt($conditions)) {
            return $conditions;
        }

        if (empty($conditions)) {
            return ['deleted_at IS NULL'];
        }

        return [
            '(', ...$conditions, ')',
            'AND', 'deleted_at IS NULL',
        ];
    }

    protected function debugSql(QueryBuilder $qb, ?string $format = null): void
    {
        $debugQuery = new DebugQuery(
            isAjax: $this->detectAjax(),
            logger: $this->logger,
        );

        $debugQuery->debugSql($qb, $format);
    }

    protected function logQuery(QueryBuilder $qb, string $operation): void
    {
        if ($this->logger && $this->logger->getDebugLevel() >= 2) {
            $this->logger->logQuery($qb, $operation);
        }
    }

    protected function extractOrderBy(array &$conditions): array
    {
        $sort = [];
        if (isset($conditions['ORDER BY'])) {
            $sort['ORDER BY'] = $conditions['ORDER BY'];
            unset($conditions['ORDER BY']);
        }
        return $sort;
    }

    private function detectAjax(): bool
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    private function shouldApplyGlobalScopes(): bool
    {
        return $this->em->getEntity() instanceof SoftDeletableInterface;
    }

    private function guardSoftDeleteUpdate(array $conditions): array
    {
        $entity = $this->resolveSoftDeletableEntity();

        if ($entity === null
            || $entity->getDeletedAt() === null
            || $this->conditionsTouchDeletedAt($conditions)
        ) {
            return $conditions;
        }

        return $this->applyGlobalScopes($conditions);
    }

    private function resolveSoftDeletableEntity(): ?SoftDeletableInterface
    {
        $entity = $this->em->getEntity();

        if ($entity instanceof SoftDeletableInterface) {
            return $entity;
        }

        if ($entity instanceof CollectionInterface && !$entity->isEmpty()) {
            $first = $entity->first();
            return $first instanceof SoftDeletableInterface ? $first : null;
        }

        if (is_array($entity) && $entity !== []) {
            $first = reset($entity);
            return $first instanceof SoftDeletableInterface ? $first : null;
        }

        return null;
    }

    private function conditionsTouchDeletedAt(array $conditions): bool
    {
        foreach ($conditions as $key => $value) {
            $token = is_string($key) ? $key : (is_string($value) ? $value : '');
            if ($token !== '' && str_starts_with($token, 'deleted_at')) {
                return true;
            }
            if (is_array($value) && $this->conditionsTouchDeletedAt($value)) {
                return true;
            }
        }
        return false;
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

    private function isValid(int|string $id): bool
    {
        if (empty($id)) {
            throw new RepositoryInvalidArgumentException('Argument should not be empty');
        }
        return true;
    }
}