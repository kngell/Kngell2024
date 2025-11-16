<?php

declare(strict_types=1);

class ColumnsParameter extends SqlQueryComponent
{
    private array $columns;
    private ColumnBuilderForSelect $ColumnBuilderForSelect;
    private bool $selectAsAlias;

    public function __construct(
        ?TablesAliasHelper $helper,
        string $table,
        bool $selectAsAlias,
        string|array ...$columns,
    ) {
        parent::__construct(); // Call parent constructor to initialize state
        $this->columns = ColumnStandardizer::standardize((array) $columns);
        $this->helper = $helper;
        $this->table = $table;
        $this->selectAsAlias = $selectAsAlias;
        $this->ColumnBuilderForSelect = new ColumnBuilderForSelect($helper, $selectAsAlias);
    }

    public function build(): string
    {
        if (!$this->helper) {
            throw new RuntimeException('TablesAliasHelper not initialized');
        }

        $columnStrings = [];
        $logicalKey = $this->getLogicalTable();

        // Use state safely - it should be initialized by now
        $tableAlias = $this->state->tableAlias ?? [];
        $aliasCheck = $this->state->aliasCheck ?? [];

        list($table, $alias) = $this->helper->get($logicalKey, $tableAlias, $aliasCheck);

        foreach ($this->columns as $column) {
            $columnStrings[] = $this->ColumnBuilderForSelect->build($column, $alias);
        }
        $this->state->tables[$logicalKey] = $this->columns;
        $this->state->tableAlias = $tableAlias;
        $this->state->aliasCheck = $aliasCheck;
        $this->query = implode(', ', $columnStrings);
        return $this->query;
    }

    private function getLogicalTable(): string
    {
        $logicalKey = $this->state->joinContext ?? $this->table;

        if (str_contains($logicalKey, '_join_')) {
            $physicalTable = explode('_join_', $logicalKey, 2)[0];
        } else {
            $physicalTable = $logicalKey;
        }

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
