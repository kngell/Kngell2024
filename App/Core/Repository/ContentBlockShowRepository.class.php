<?php

declare(strict_types=1);

class ContentBlockShowRepository extends Repository
{
    protected const array COLUMN_MAPS = [
        'content_block' => [
            'id', 'section_id', 'block_type', 'title', 'subtitle', 'page_target', 'button_text', 'button_text', 'button_link',
            'sort_order', 'is_active', 'block_metadata', 'valid_from',
            'valid_to', 'created_at', 'updated_at',
        ],
        'product' => ['pdt_id', 'name', 'description', 'slug', 'main_image', 'short_description', 'sku'],
        'category' => ['cat_id', 'name', 'icon', 'image_url', 'price_ranges'],
    ];

    public function fetchIds(array $conditions = [], ?int $limit = null, ?int $offset = null, ?string $keyField = null): void
    {
        $mode = $this->extractModeFromConditions($conditions);
        $baseConditions = $this->buildModeConditions($conditions, $mode);
        $baseConditions = $this->removeModeFlags($baseConditions);

        $keyField = $keyField ?? $this->em->getEntityKeyField();
        $columns = [$keyField];

        $qb = $this->em->createQueryBuilder();
        $queryBuilder = $qb
            ->select($columns);

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
        $baseConditions = $this->removeModeFlags($baseConditions);

        if ($mode === ConditionListMode::MODE_FRONTEND->value) {
            $this->executeFrontendQuery($baseConditions, $columns, $limit, $offset);
            return;
        }

        // Use enriched query builder with proper aliases
        $qb = $this->em->createQueryBuilder();
        $select = $this->getEnrichedQueryBuilder($baseConditions, $columns, $qb);

        if ($limit !== null) {
            $select->limit($limit);
        }
        if ($offset !== null) {
            $select->offset($offset);
        }

        $select->build();
        // $this->debugSql($qb);
    }

    public function findOneBy(array $conditions = [], array $columns = []): void
    {
        $mode = $this->extractModeFromConditions($conditions);
        $baseConditions = $this->buildModeConditions($conditions, $mode);
        $baseConditions = $this->removeModeFlags($baseConditions);

        if ($mode === ConditionListMode::MODE_FRONTEND->value) {
            $this->executeFrontendQuery($baseConditions, $columns, 1, null);
            return;
        }
        $columns = empty($columns) ? null : $columns;
        $qb = $this->em->createQueryBuilder();
        $select = $this->getEnrichedQueryBuilder($baseConditions, $columns, $qb);
        $select->limit(1);
        $select->build();
        // $this->debugSql($qb);
    }

    public function findByID(int|string $id): void
    {
        $fieldId = $this->em->getEntityKeyField();
        $this->findOneBy([$fieldId => $id]);
    }

    public function count(array $conditions): void
    {
        $mode = $this->extractModeFromConditions($conditions);
        $baseConditions = $this->buildModeConditions($conditions, $mode);
        $baseConditions = $this->removeModeFlags($baseConditions);

        // For count, use CTE approach like ProductShow
        $qb = $this->em->createQueryBuilder();
        $qb->with('countbl')
            ->body($this->getEnrichedQueryBuilder($baseConditions, ['id'], $qb))
            ->mainquery(
                $qb->select('count(*) as totalRecord')
                    ->from('countbl'),
            )->build();
        // $this->debugSql($qb);
    }

    protected function buildModeConditions(array $conditions, ?string $mode): array
    {
        switch ($mode) {
            case ConditionListMode::MODE_ADMIN->value:
                // Admin: apply global scopes (deleted_at IS NULL)
                return $this->applyGlobalScopes($conditions);
            case ConditionListMode::MODE_RESTORABLE->value:
                // Restorable: only soft-deleted
                return array_merge(['deleted_at!' => null], $conditions);
            case ConditionListMode::MODE_FRONTEND->value:
                // Frontend: active + valid date range + global scopes
                $conditions = array_merge(
                    ['is_active' => true],
                    $this->createDateRangeCondition('valid_from', 'NOW()', '<='),
                    $this->createDateRangeCondition('valid_to', 'NOW()', '>='),
                    $conditions,
                );
                return $this->applyGlobalScopes($conditions);
            default:
                // Default: apply global scopes only
                return $this->applyGlobalScopes($conditions);
        }
    }

    private function getEnrichedQueryBuilder(array $conditions, ?array $columns, SqlCompositeQueryBuilderInterface $qb): SqlSelectQueryBuilderInterface
    {
        $isFullQuery = ($columns === null);
        $selectedColumns = $columns ?? $this->getAllColumns();

        $select = $qb->selectWithAlias($selectedColumns)
            ->distinct()
            ->from('content_block');
        $this->applySmartJoins($select, $conditions, $isFullQuery);

        $conditions = $this->applySqlKeywordsForSelect($conditions, $select);

        $conditions = $this->applyGlobalScopes($conditions);

        $this->applyMixedConditions($select, $conditions);

        // Default ordering
        if ($isFullQuery) {
            $select->orderBy('sort_order ASC', 'created_at DESC');
        }

        return $select;
    }

    private function applySmartJoins(SqlSelectQueryBuilderInterface $qb, array $conditions, bool $isFullQuery): void
    {
        $productCols = $isFullQuery ? self::COLUMN_MAPS['product'] : [];
        $categoryCols = $isFullQuery ? self::COLUMN_MAPS['category'] : [];

        // Join product if needed (for product_id)
        if ($isFullQuery || $this->isFilteringByTable($conditions, 'product')) {
            $qb->leftJoin('product', $productCols)
               ->on(['product_id' => 'product.pdt_id']);
        }

        // Join category if needed
        if ($isFullQuery || $this->isFilteringByTable($conditions, 'category')) {
            $qb->leftJoin('category', $categoryCols)
               ->on(['category_id' => 'category.cat_id']);
        }
    }

    private function isFilteringByTable(array $conditions, string $tableName): bool
    {
        foreach (array_keys($conditions) as $key) {
            if (str_contains((string) $key, $tableName)) {
                return true;
            }
        }
        return false;
    }

    private function executeFrontendQuery(array $conditions, array $columns = [], ?int $limit = null, ?int $offset = null): void
    {
        $qb = $this->em->createQueryBuilder();
        $query = $qb->select($columns ?: '*')->from('content_block');

        $caseConditions = $this->extractCaseConditions($conditions);

        $this->applyMixedConditions($query, $conditions);

        if (!empty($caseConditions)) {
            $query = $query->orderBy(
                $qb->case()->when($caseConditions)->then(0)->else(1)->end(),
                'ASC',
            );
        }

        // Set limit and offset
        if ($limit !== null) {
            $query->limit($limit);
        }

        if ($offset !== null) {
            $query->offset($offset);
        }

        $query->build();
        // if ($conditions['block_type'] === 'big_banner') {
        //     $this->debugSql($qb);
        // }
    }
}