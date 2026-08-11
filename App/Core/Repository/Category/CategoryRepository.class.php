<?php

declare(strict_types=1);

class CategoryRepository extends Repository
{
    public function findAll(array $conditions = [], ?int $limit = null, ?int $offset = null, array $columns = []): void
    {
        $columns = empty($columns) ? ['cat_id', 'parent_id', 'name', 'order_index'] : $columns;
        $columns = ['*'];

        $qbFactory = $this->executeRecursiveQuery($conditions, $columns, $limit, $offset);
        // $this->debugSql($qbFactory);
    }

    public function count(array $conditions): void
    {
        $sort['ORDER BY'] = $conditions['ORDER BY'] ?? 'newest';
        unset($conditions['ORDER BY']);

        $mode = $this->extractModeFromConditions($conditions);
        $baseConditions = $this->buildModeConditions($conditions, $mode);

        $qbFactory = $this->em->createQueryBuilder();
        $select = $qbFactory->select('COUNT(cat_id) as total_record')
        ->from('category');
        $this->applyMixedConditions($select, $baseConditions);

        $select->build();
        // $this->debugSql($qbFactory);
    }

    public function fetchIds(array $conditions = [], ?int $limit = null, ?int $offset = null, ?string $keyField = 'cat_id'): void
    {
        $sortPayload['ORDER BY'] = $conditions['ORDER BY'] ?? ['order_index' => 'ASC', 'name' => 'ASC'];
        unset($conditions['ORDER BY']);

        $mode = $this->extractModeFromConditions($conditions);
        $baseConditions = $this->buildModeConditions($conditions, $mode);
        $baseConditions = $this->removeModeFlags($baseConditions);

        if ($this->hasLimitOrOffset($baseConditions)) {
            [$baseConditions, $extractedLimit, $extractedOffset] = $this->extractLimitOffset($baseConditions);
            $limit = $limit ?? $extractedLimit;
            $offset = $offset ?? $extractedOffset;
        }

        if ($mode === ConditionListMode::MODE_ADMIN->value && !isset($baseConditions['parent_id'])) {
            $baseConditions['parent_id'] = 'IS NULL';
        }

        $qbFactory = $this->em->createQueryBuilder();
        $select = $qbFactory->select('cat_id')->from('category');

        $this->applyMixedConditions($select, $baseConditions);
        $this->applySqlKeywords($sortPayload, $select);

        if ($limit !== null) {
            $select->limit($limit);
        }
        if ($offset !== null) {
            $select->offset($offset);
        }

        $select->build();
        // $this->debugSql($qbFactory);
    }

    protected function buildModeConditions(array $conditions, ?string $mode): array
    {
        switch ($mode) {
            case ConditionListMode::MODE_ADMIN->value:
                return $this->applyGlobalScopes($conditions);
            case ConditionListMode::MODE_RESTORABLE->value:
                return array_merge(['deleted_at' => 'is not null'], $conditions);
            case ConditionListMode::MODE_FRONTEND->value:
                return $this->applyGlobalScopes(array_merge(['is_active' => true], $conditions));
            default:
                return $conditions;
        }
    }

    private function executeRecursiveQuery(
        array $conditions,
        array $columns,
        ?int $limit = null,
        ?int $offset = null,
        bool $isCount = false,
    ): ?QueryBuilder {
        try {
            $sort['ORDER BY'] = $conditions['ORDER BY'] ?? 'newest';
            unset($conditions['ORDER BY']);
            $mode = $this->extractModeFromConditions($conditions);
            $baseConditions = $this->buildModeConditions($conditions, $mode);
            $qbFactory = $this->em->createQueryBuilder();

            if ($this->hasLimitOrOffset($baseConditions)) {
                [$conditions, $extractedLimit, $extractedOffset] = $this->extractLimitOffset($conditions);
                $limit = $limit ?? $extractedLimit;
                $offset = $offset ?? $extractedOffset;
            }

            $anchorSubQuery = $qbFactory
                ->select($columns)
                ->from('category', 'custom');

            if (!$this->hasStartingNodeCondition($conditions)) {
                $anchorSubQuery->whereNull('parent_id');
            }
            // dd($conditions);

            $this->applyMixedConditions($anchorSubQuery, $conditions);
            if (!isset($conditions['ORDER BY'])) {
                $anchorSubQuery->orderBy('order_index ASC', 'name ASC');
            } else {
                $conditions = $this->applySqlKeywords($conditions, $anchorSubQuery);
            }

            if ($mode === ConditionListMode::MODE_ADMIN->value && ($limit !== null || $offset !== null)) {
                if ($limit !== null) {
                    $anchorSubQuery->limit($limit);
                }
                if ($offset !== null) {
                    $anchorSubQuery->offset($offset);
                }
            }

            $anchor = $qbFactory
                ->select(array_merge($columns, ["CAST(LPAD(order_index, 5, '0') AS CHAR(255)) AS sort_path"]))
                ->from($anchorSubQuery);

            $recursive = $qbFactory
                ->select(array_merge($columns, ["CONCAT(category_tree.sort_path, '-', LPAD(order_index, 5, '0')) AS sort_path"]))
                ->from('category')
                ->innerJoin('category_tree')->on('parent_id', 'cat_id')
                ->where('deleted_at IS NULL');

            $mainQueryColumns = $isCount ? ['COUNT(cat_id) as total_record'] : $columns;
            $mainQuery = $qbFactory->select($mainQueryColumns)->from('category_tree')->orderBy('sort_path ASC');

            // Strategy B (Frontend): Limit final flat data selection block
            if ($mode === ConditionListMode::MODE_FRONTEND->value && !$isCount && ($limit !== null || $offset !== null)) {
                if ($limit !== null) {
                    $mainQuery->limit($limit);
                }
                if ($offset !== null) {
                    $mainQuery->offset($offset);
                }
            }

            $qbFactory->withRecursive('category_tree')
                ->body($anchor->unionAll($recursive))
                ->mainQuery($mainQuery)
                ->build();

            return $qbFactory;
        } catch (Throwable $th) {
            error_log('Error in CategoryRepository::executeRecursiveQuery: ' . $th->getMessage());
            return null;
        }
    }

    private function hasStartingNodeCondition(array $conditions): bool
    {
        foreach ($conditions as $key => $value) {
            if (is_string($key) && (str_contains($key, 'cat_id') || str_contains($key, 'parent_id'))) {
                return true;
            }
            if (is_string($value) && (str_contains($value, 'cat_id') || str_contains($value, 'parent_id'))) {
                return true;
            }
            if (is_array($value) && $this->hasStartingNodeCondition($value)) {
                return true;
            }
        }
        return false;
    }
}