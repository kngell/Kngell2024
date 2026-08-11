<?php

declare(strict_types=1);

abstract class AbstractFooterRepository extends Repository
{
    /**
     * Shared fetch IDs logic - identical across all footer repositories.
     */
    public function fetchIds(array $conditions = [], ?int $limit = null, ?int $offset = null, ?string $keyField = null): void
    {
        $mode = $this->extractModeFromConditions($conditions);
        $baseConditions = $this->buildModeConditions($conditions, $mode);

        $keyField = $keyField ?? $this->em->getEntityKeyField();
        $columns = [$keyField];

        $qb = $this->em->createQueryBuilder();
        $queryBuilder = $qb->select($columns);

        [$baseConditions, $extLimit, $extOffset] = $this->extractLimitOffset($baseConditions);

        $sort = [];
        if (isset($baseConditions['ORDER BY'])) {
            $sort['ORDER BY'] = $baseConditions['ORDER BY'];
            unset($baseConditions['ORDER BY']);
        }

        $this->applyMixedConditions($queryBuilder, $baseConditions);

        if (!empty($sort)) {
            $this->applySqlKeywords($sort, $queryBuilder);
        }

        $limit = $limit ?? $extLimit;
        $offset = $offset ?? $extOffset;

        if ($limit !== null) {
            $queryBuilder->limit($limit);
        }
        if ($offset !== null) {
            $queryBuilder->offset($offset);
        }

        $queryBuilder->build();
    }

    public function count(array $conditions): void
    {
        $mode = $this->extractModeFromConditions($conditions);
        $baseConditions = $this->buildModeConditions($conditions, $mode);
        parent::count($baseConditions);
    }

    public function findOneBy(array $conditions = [], array $columns = []): void
    {
        $mode = $this->extractModeFromConditions($conditions);
        $baseConditions = $this->buildModeConditions($conditions, $mode);

        parent::findOneBy($baseConditions);
    }

    public function findBy(array $conditions = [], ?int $limit = null, ?int $offset = null, array $columns = []): void
    {
        $mode = $this->extractModeFromConditions($conditions);
        $baseConditions = $this->buildModeConditions($conditions, $mode);

        // Social doesn't have special queries, just use parent
        parent::findBy($baseConditions, $limit, $offset, $columns);
    }

    protected function createDateRangeCondition(string $field, string $now = 'NOW()', string $operator = '<='): array
    {
        return [
            '(', "$field IS NULL", 'OR', $field, $operator, $now, ')',
        ];
    }

    /**
     * Shared mode condition builder.
     */
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

    /**
     * Shared helper to extract case conditions.
     */
    protected function extractCaseConditions(array &$conditions): array
    {
        $caseConditions = [];
        if (isset($conditions[SpecialConditions::CASE->value])) {
            $caseConditions = $conditions[SpecialConditions::CASE->value];
            unset($conditions[SpecialConditions::CASE->value]);
        }
        return $caseConditions;
    }
}