<?php

declare(strict_types=1);

class ContentBlockRepository extends Repository
{
    public function fetchIds(array $conditions = [], ?int $limit = null, ?int $offset = null, ?string $keyField = null): void
    {
        $mode = $this->extractModeFromConditions($conditions);
        $baseConditions = $this->buildModeConditions($conditions, $mode);

        $keyField = $keyField ?? $this->em->getEntityKeyField();
        $columns = [$keyField];

        $qb = $this->em->createQueryBuilder();
        $queryBuilder = $qb->select($columns);

        $sort = $this->extractOrderBy($baseConditions);
        if ($this->hasLimitOrOffset($baseConditions)) {
            [$baseConditions, $extractedLimit, $extractedOffset] = $this->extractLimitOffset($baseConditions);
            $limit = $limit ?? $extractedLimit;
            $offset = $offset ?? $extractedOffset;
        }

        $this->applyMixedConditions($queryBuilder, $baseConditions);

        if (!empty($sort)) {
            $baseConditions = $this->applySqlKeywords($baseConditions, $queryBuilder);
        }

        if ($limit !== null) {
            $queryBuilder->limit($limit);
        }
        if ($offset !== null) {
            $queryBuilder->offset($offset);
        }

        $queryBuilder->build();
        // $this->debugSql($qb);
    }

    public function findBy(array $conditions = [], ?int $limit = null, ?int $offset = null, array $columns = []): void
    {
        $mode = $this->extractModeFromConditions($conditions);
        $baseConditions = $this->buildModeConditions($conditions, $mode);

        if ($mode === ConditionListMode::MODE_FRONTEND->value) {
            $this->executeFrontendQuery($baseConditions, $columns, $limit, $offset);
            return;
        }

        parent::findBy($baseConditions, $limit, $offset, $columns);
    }

    public function findOneBy(array $conditions = [], array $columns = []): void
    {
        $mode = $this->extractModeFromConditions($conditions);
        $baseConditions = $this->buildModeConditions($conditions, $mode);

        if ($mode === ConditionListMode::MODE_FRONTEND->value) {
            $this->executeFrontendQuery($baseConditions, $columns);
            return;
        }
        parent::findOneBy($baseConditions);
    }

    public function count(array $conditions): void
    {
        $mode = $this->extractModeFromConditions($conditions);
        $baseConditions = $this->buildModeConditions($conditions, $mode);

        parent::count($baseConditions);
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

    private function executeFrontendQuery(array $conditions, array $columns = [], ?int $limit = null, ?int $offset = null): void
    {
        $qb = $this->em->createQueryBuilder();
        $query = $qb->select($columns ?: '*')->from('content_block');

        $caseConditions = $this->extractCaseConditions($conditions);
        $this->applyMixedConditions($query, $conditions);

        if (!empty($caseConditions)) {
            $query = $query->orderBy($qb->case()->when($caseConditions)->then(0)->else(1)->end(), 'ASC');
        }

        $query->orderBy(
            $qb->case()
                ->when('valid_from IS NOT NULL AND valid_from <= NOW()')->then(0)
                ->when('valid_from IS NULL')->then(1)
                ->else(2)->end(),
            'ASC',
        )
        ->orderBy('sort_order', 'ASC')
        ->orderBy('created_at', 'DESC');

        if ($limit !== null) {
            $query->limit($limit);
        }
        if ($offset !== null) {
            $query->offset($offset);
        }

        $query->build();
        // $this->debugSql($qb);
    }
}