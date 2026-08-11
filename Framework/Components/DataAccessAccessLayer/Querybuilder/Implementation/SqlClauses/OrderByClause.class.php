<?php

declare(strict_types=1);
class OrderByClause extends SqlComponent implements RegularClauseComponentInterface
{
    private const SqlClause CLAUSE = SqlClause::ORDER_BY;

    public function __construct(
        private mixed $orderByColumns,
        ?string $table,
    ) {
        parent::__construct();
        $this->table = $table;
    }

    public function build(): string
    {
        if (!$this->helper) {
            throw new RuntimeException('TablesAliasHelper not initialized');
        }
        $tableAlias = $this->state->tableAlias;
        $aliasCheck = $this->state->aliasCheck;
        $this->helper->setTables($this->state->tables);

        $orderBy = $this->getOrderByColumns();
        $newColumns = [];

        // Process every sorting rule passed to the builder
        foreach ($orderBy as $mixedClause) {
            $compiledItems = $this->processSortItem($mixedClause);
            foreach ($compiledItems as $item) {
                $newColumns[] = $item;
            }
        }

        $this->state->tableAlias = $tableAlias;
        $this->state->aliasCheck = $aliasCheck;

        $this->query = implode(', ', $newColumns);
        return $this->query;
    }

    /**
     * @return null|SqlClause
     */
    public function getSqlClause(): null|SqlClause
    {
        return self::CLAUSE;
    }

    /**
     * * @return string[]
     */
    private function processSortItem(mixed $item, string $inheritedDirection = 'ASC'): array
    {
        if ($item instanceof SqlComponent) {
            $this->prepareChild($item);
            $compiledSql = $item->build();
            $this->mergeChildState($item);
            return [trim($compiledSql) . ' ' . $inheritedDirection];
        }

        if (is_array($item)) {
            if (count($item) === 2 && isset($item[0]) && isset($item[1]) && is_string($item[1]) && in_array(strtolower($item[1]), ['asc', 'desc'])) {
                return $this->processSortItem($item[0], strtoupper($item[1]));
            }

            $results = [];
            foreach ($item as $key => $value) {
                if (is_string($key)) {
                    // Assoc pair pattern: ["column" => "DESC"]
                    $combinedString = $key . ' ' . (is_string($value) ? $value : $inheritedDirection);
                    $results = array_merge($results, $this->processSortItem($combinedString));
                } else {
                    // Nested plain element pattern
                    $results = array_merge($results, $this->processSortItem($value, $inheritedDirection));
                }
            }
            return $results;
        }

        // 3. Normalized String Handler (e.g., "priority DESC" or "c.sort_order")
        if (is_string($item)) {
            list($rawColumn, $sortDirection) = $this->ascDescColumnparser($item);
            if (empty($sortDirection) || $sortDirection === 'ASC') {
                $sortDirection = $inheritedDirection;
            }

            $logicalTable = $this->table;
            if (str_contains($rawColumn, '.')) {
                list($logicalTable, $rawColumn) = $this->helper->mapTableColumn($rawColumn);
            }

            list($table, $alias) = $this->helper->get($logicalTable, $this->state->tableAlias, $this->state->aliasCheck);
            $aliasPrefix = $this->getAliasPrefix($alias);

            return [$aliasPrefix . $rawColumn . ' ' . $sortDirection];
        }

        return [];
    }

    private function getOrderByColumns(): array
    {
        $orderBy = $this->orderByColumns;
        $normalized = [];
        foreach ($orderBy as $key => $value) {
            if (is_int($key)) {
                // Case: ['column ASC', 'other_column DESC']
                $normalized[] = $value;
            } else {
                // Case: ['column' => 'ASC']
                $normalized[] = $key . ' ' . $value;
            }
        }

        return $normalized;
    }

    private function ascDescColumnparser(string $column): array
    {
        $parts = preg_split('/\s+/', trim($column));
        $col = $parts[0];
        $dir = (isset($parts[1]) && in_array(strtolower($parts[1]), ['asc', 'desc']))
               ? strtoupper($parts[1])
               : 'ASC';

        return [$col, $dir];
    }
}