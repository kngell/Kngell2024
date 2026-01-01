<?php

declare(strict_types=1);

class ColumnsParameter extends SqlComponent
{
    private array $columns;
    private ColumnBuilderForSelect $ColumnBuilderForSelect;
    private bool $selectAsAlias;

    public function __construct(
        ?TablesAliasHelper $helper,
        null|string|Closure $table,
        bool $selectAsAlias,
        string|array ...$columns,
    ) {
        parent::__construct(); // Call parent constructor to initialize state
        $this->columns = ColumnStandardizer::standardize((array) $columns);
        $this->helper = $helper;
        $this->table = $table;
        $this->selectAsAlias = $selectAsAlias;
        $this->ColumnBuilderForSelect = new ColumnBuilderForSelect($selectAsAlias);
    }

    public function build(): string
    {
        if (!$this->helper) {
            throw new RuntimeException('TablesAliasHelper not initialized');
        }
        $this->ensureStateInitialized();

        if (!$this->table instanceof Closure) {
            $columnStrings = [];
            $logicalKey = $this->getLogicalTable();

            $tableAlias = $this->state->tableAlias ?? [];
            $aliasCheck = $this->state->aliasCheck ?? [];
            if ($this->customAlias === null) {
                list($table, $alias) = $this->helper->get($logicalKey, $tableAlias, $aliasCheck);
            } else {
                $alias = $this->customAlias;
            }
        } else {
            $query = new SqlQueryClosure($this->table);
            $innerSql = $query->build();
            $this->mergeChildState($query);
            return $innerSql;
        }

        foreach ($this->columns as $column) {
            $columnStrings[] = $this->ColumnBuilderForSelect->build($column, $alias);
        }
        if (isset($logicalKey)) {
            $this->state->tables[$logicalKey] = $this->columns;
            $this->state->tableAlias = $tableAlias;
            $this->state->aliasCheck = $aliasCheck;
        }

        $this->query = implode(', ', $columnStrings);
        return $this->query;
    }

    private function getLogicalTable(): string
    {
        $logicalKey = $this->state->joinContext ?? $this->table;

        $physicalTable = $this->helper->getPhysicalTable($logicalKey);

        $columns = [];
        foreach ($this->columns as $column) {
            if (is_string($column) && str_contains($column, '.')) {
                $parts = explode('.', $column, 2);
                if (count($parts) === 2 && $physicalTable === $parts[0]) {
                    $columns[] = $parts[1];
                } else {
                    $columns[] = $column;
                }
            } else {
                $columns[] = $column;
            }
        }
        $this->columns = $columns;
        // Update state immutably
        $this->state = $this->state->withLogicalToPhysicalMap(
            array_merge($this->state->logicalToPhysicalMap, [$logicalKey => $physicalTable]),
        );

        return $logicalKey;
    }

    // Optional: Add state validation
    private function ensureStateInitialized(): void
    {
        if (!$this->state) {
            throw new RuntimeException('QueryState not initialized in ColumnsParameter');
        }
    }
}