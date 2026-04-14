<?php

declare(strict_types=1);
class OrderByClause extends SqlComponent implements RegularClauseComponentInterface
{
    private const SqlClause CLAUSE = SqlClause::ORDER_BY;

    public function __construct(
        private array $orderByColumns,
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

        foreach ($orderBy as $key => $column) {
            list($column, $sort) = $this->ascDescColumnparser($column);
            $logicalTable = $this->table;
            if (str_contains($column, '.')) {
                list($logicalTable, $column) = $this->helper->mapTableColumn($column);
            }
            list($table, $alias) = $this->helper->get($logicalTable, $tableAlias, $aliasCheck);
            $alias = !empty($alias) ? $alias . '.' : '';
            $newColumns[] = $alias . $column . ' ' . $sort;
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

    private function getOrderByColumns(): array
    {
        $orderBy = $this->orderByColumns;
        if (ArrayUtils::isMultidimentional($orderBy) && count($orderBy) === 1) {
            $orderBy = ArrayUtils::first($orderBy);
        }

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

    private function normalizeColumn(array $columns): array
    {
        $newColumns = [];
        if (isset($columns[1]) && in_array(strtolower($columns[1]), ['asc', 'desc'])) {
            $newColumns[] = $columns[0] . ' ' . $columns[1];
            unset($columns[0]);
            unset($columns[1]);
            $columns = array_values($columns);
            if (!empty($columns)) {
                $newColumns = array_merge($newColumns, $this->normalizeColumn($columns));
            }
            return $newColumns;
        }
        return $columns;
    }
}