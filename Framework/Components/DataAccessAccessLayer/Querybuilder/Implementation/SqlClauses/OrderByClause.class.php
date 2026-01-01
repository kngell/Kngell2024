<?php

declare(strict_types=1);
class OrderByClause extends SqlComponent implements RegularClauseComponentInterface
{
    private const SqlClause CLAUSE = SqlClause::ORDER_BY;

    public function __construct(
        private array $orderByColumns = [],
    ) {
        parent::__construct(self::CLAUSE);
    }

    public function build(): string
    {
        if (!$this->helper) {
            throw new RuntimeException('TablesAliasHelper not initialized');
        }
        $tableAlias = $this->state->tableAlias;
        $aliasCheck = $this->state->aliasCheck;

        $orderBy = $this->getOrderByComlumns();
        $newColumns = [];

        foreach ($orderBy as $key => $column) {
            list($column, $sort) = $this->ascDescColumnparser($column);
            list($table, $column) = $this->helper->mapTableColumn($column);
            list($table, $alias) = $this->helper->get($table, $tableAlias, $aliasCheck);
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

    private function getOrderByComlumns(): array
    {
        $orderBy = $this->orderByColumns;
        if (ArrayUtils::isMultidimentional($orderBy)) {
            $orderBy = ArrayUtils::first($orderBy);
            if (ArrayUtils::isAssoc($orderBy)) {
                $orderBy = ArrayUtils::FromAssocToSequential($orderBy);
                $columns = [];
                foreach ($orderBy as $col => $sort) {
                    if (is_int($col)) {
                        $columns[] = $sort;
                    } else {
                        $columns[] = $col . ' ' . $sort;
                    }
                }
                return $columns;
            } else {
                $orderBy = ArrayUtils::flattenArrayRecursive($orderBy);
            }
        }
        return $orderBy;
    }

    private function ascDescColumnparser(string $column): array
    {
        $columns = explode(' ', $column);
        if (count($columns) === 1) {
            return [$column, ''];
        }
        if (count($columns) === 2 && in_array(strtolower($columns[1]), ['asc', 'desc'])) {
            return [$columns[0], strtoupper($columns[1])];
        }
        return [];
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