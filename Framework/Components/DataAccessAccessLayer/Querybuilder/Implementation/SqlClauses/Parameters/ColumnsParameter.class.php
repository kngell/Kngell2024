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
        bool $distinct,
        ?EntityManagerInterface $em = null,
        private ?StatementType $clauseContext = null,
        string|array ...$columns,
    ) {
        parent::__construct(em:$em); // Call parent constructor to initialize state
        $this->columns = ColumnStandardizer::standardize((array) $columns);
        $this->helper = $helper;
        $this->table = $table;
        $this->selectAsAlias = $selectAsAlias;
        $this->distinct = $distinct;
        $this->ColumnBuilderForSelect = new ColumnBuilderForSelect($selectAsAlias, $distinct);
    }

    public function build(): string
    {
        if (!$this->helper) {
            throw new RuntimeException('TablesAliasHelper not initialized');
        }

        $this->ensureStateInitialized();
        if ($this->table instanceof Closure) {
            return $this->buildSubquery();
        }

        return $this->buildRegularColumns();
    }

    private function buildSubquery(): string
    {
        $query = new SqlRowClosure($this->table, $this->em, $this->clauseContext);
        $innerSql = $query->build();
        $this->mergeChildState($query);
        return $innerSql;
    }

    private function buildRegularColumns(): string
    {
        $logicalKey = $this->getLogicalTable();
        $columnStrings = [];
        foreach ($this->columns as $column) {
            $columnStrings[] = $this->ColumnBuilderForSelect->build(
                $column,
                $this->helper,
                $this->state,
                $logicalKey,
                $this->customAlias,
            );
        }
        $this->query = implode(', ', $columnStrings);
        return $this->query;
    }

    // private function buildRegularColumns(): string
    // {
    //     $logicalKey = $this->getLogicalTable();

    //     // Get table alias
    //     $tableAlias = $this->state->tableAlias ?? [];
    //     $aliasCheck = $this->state->aliasCheck ?? [];
    //     $this->helper->setCustomAlias($this->customAlias);

    //     [$table, $alias] = $this->helper->get($logicalKey, $tableAlias, $aliasCheck);
    //     if ($logicalKey === 'category_tree') {
    //         $stop = true;
    //     }
    //     // Build column strings
    //     $columnStrings = [];
    //     foreach ($this->columns as $column) {
    //         $columnStrings[] = $this->ColumnBuilderForSelect->build($column, $alias);
    //     }

    //     // Update state
    //     $this->state->tables[$logicalKey] = $this->columns;
    //     $this->state->tableAlias = $tableAlias;
    //     $this->state->aliasCheck = $aliasCheck;

    //     $this->query = implode(', ', $columnStrings);
    //     return $this->query;
    // }

    private function getLogicalTable(): string
    {
        $logicalKey = $this->table;
        $physicalTable = $this->helper->getPhysicalTable($logicalKey);

        $this->state = $this->state->withLogicalToPhysicalMap(
            array_merge($this->state->logicalToPhysicalMap, [$logicalKey => $physicalTable]),
        );

        // $this->columns = $this->cleanColumnPrefixes($this->columns, $physicalTable, $logicalKey);

        return $logicalKey;
    }

    private function cleanColumnPrefixes(array $columns, string $physicalTable, string $logicalKey): array
    {
        return array_map(function ($column) use ($physicalTable, $logicalKey) {
            if (!is_string($column) || !str_contains($column, '.')) {
                return $column;
            }

            $parts = explode('.', $column);

            if (count($parts) >= 3) {
                $possiblePhysicalTable = $parts[count($parts) - 2];
                $columnName = end($parts);

                // Check if this matches the expected physical table
                if ($possiblePhysicalTable === $physicalTable) {
                    return $columnName;
                }

                // Also check against logical key mappings
                $logicalToPhysicalMap = $this->state->logicalToPhysicalMap ?? [];
                if (isset($logicalToPhysicalMap[$possiblePhysicalTable]) &&
                    $logicalToPhysicalMap[$possiblePhysicalTable] === $physicalTable) {
                    return $columnName;
                }
            }

            // Handle simple table.column format
            $firstPrefix = $parts[0];
            if ($firstPrefix === $physicalTable || $firstPrefix === $logicalKey) {
                return end($parts);
            }

            return $column;
        }, $columns);
    }

    private function ensureStateInitialized(): void
    {
        if (!$this->state) {
            throw new RuntimeException('QueryState not initialized in ColumnsParameter');
        }
    }
}