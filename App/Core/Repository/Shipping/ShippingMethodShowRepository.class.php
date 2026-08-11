<?php

declare(strict_types=1);

class ShippingMethodShowRepository extends Repository
{
    protected const array COLUMN_MAPS = [
        'shipping_method' => [
            'id', 'code', 'name', 'description', 'carrier', 'shipping_method_type',
            'is_active', 'is_default', 'sort_order', 'settings',
            'min_delivery_days', 'max_delivery_days', 'created_at', 'updated_at',
        ],
        'shipping_rate' => [
            'id', 'method_id', 'zone_id', 'min_value', 'max_value',
            'rate_type', 'rate_value', 'currency', 'conditions',
            'is_active', 'created_at', 'updated_at',
        ],
        'shipping_zone' => [
            'id', 'name', 'code', 'description',
            'is_active', 'sort_order', 'settings',
            'created_at', 'updated_at',
        ],
        'country' => [
            'id', 'iso_code', 'iso3_code', 'official_name',
        ],
    ];

    public function findBy(
        array $conditions = [],
        ?int $limit = null,
        ?int $offset = null,
        array $columns = [],
    ): void {
        $mode = $this->extractModeFromConditions($conditions);
        $baseConditions = $this->buildModeConditions($conditions, $mode);
        $baseConditions = $this->removeModeFlags($baseConditions);

        // Use parent's getAllColumns method
        $columns = array_merge($columns, $this->getAllColumns());

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
        $baseConditions = $this->removeModeFlags($baseConditions);

        $columns = array_merge($columns, $this->getAllColumns());

        if ($mode === ConditionListMode::MODE_FRONTEND->value) {
            $this->executeFrontendQuery($baseConditions, $columns, 1, 0);
            return;
        }

        parent::findOneBy($baseConditions, $columns);
    }

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
                    $conditions,
                );
                return $this->applyGlobalScopes($conditions);
            default:
                return $conditions;
        }
    }

    private function executeFrontendQuery(
        array $conditions,
        array $columns = [],
        ?int $limit = null,
        ?int $offset = null,
    ): void {
        $qb = $this->em->createQueryBuilder();

        $query = $qb->selectWithAlias($columns ?: '*')
            ->from('shipping_method')
            ->innerJoin('shipping_rate')
            ->on('id', 'shipping_rate.method_id')
            ->innerJoin('shipping_zone')
            ->on('shipping_rate.zone_id', 'shipping_zone.id')
            ->innerJoin('shipping_zone_country')
            ->on('shipping_zone.id', 'shipping_zone_country.zone_id')
            ->innerJoin('shipping_zone.country')
            ->on('shipping_zone_country.id', 'country.id');

        if ($this->hasLimitOrOffset($conditions)) {
            [$conditions, $extractedLimit, $extractedOffset] = $this->extractLimitOffset($conditions);
            $limit = $limit ?? $extractedLimit;
            $offset = $offset ?? $extractedOffset;
        }

        if (!empty($conditions)) {
            $this->applyMixedConditions($query, $conditions);
        }

        $query->orderBy(
            'is_default DESC',
            'sort_order ASC',
        );

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