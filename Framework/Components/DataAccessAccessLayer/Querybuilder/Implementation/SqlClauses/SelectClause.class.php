<?php

declare(strict_types=1);

class SelectClause extends SqlQuery implements RegularClauseComponentInterface
{
    private const SqlClause CLAUSE = SqlClause::SELECT;

    public function __construct(
        private ColumnCollector $columnsCollector,
        ?EntityManagerInterface $em,
        private ?StatementType $clauseContext,
    ) {
        parent::__construct(self::CLAUSE, null, $em);
        $this->withAlias = $columnsCollector->getWithAlias();
        $this->distinct = $columnsCollector->getDistinct();
        $this->customAlias = $columnsCollector->getCustomAlias();
    }

    public function build(): string
    {
        if (!$this->helper) {
            throw new RuntimeException('TablesAliasHelper not initialized');
        }

        // Initialize columns now that we have state
        if ($this->children->count() === 0) {
            $this->initializeColumns();
        }

        $columnStrings = [];
        foreach ($this->children as $child) {
            $this->prepareChild($child);
            $columnStrings[] = $child->build();
            $this->mergeChildState($child);
        }

        $this->query = implode(', ', array_filter($columnStrings));
        return $this->query;
    }

    public function getSqlClause(): ?SqlClause
    {
        return self::CLAUSE;
    }

    public function getSuffix(): string
    {
        if ($this->distinct) {
            return ' DISTINCT ';
        }
        return '';
    }

    public function isComposite(): bool
    {
        return true;
    }

    private function initializeColumns(): void
    {
        $columnMap = $this->dispatchColumns($this->columnsCollector->all());

        foreach ($columnMap as $key => $config) {
            $isMainTable = array_key_first($columnMap) === $key;

            // Skip empty non-main tables
            if (!$isMainTable && empty($config['columns'])) {
                continue;
            }

            $table = $config['table'] ?? $this->extractTableFromKey($key);
            $columns = $this->standardizeColumns($config['columns'] ?? [], $isMainTable);
            $customAlias = $config['customAlias'] ?? null;
            $tableWithAlias = $config['withAlias'] ?? $this->withAlias;

            $parameter = new ColumnsParameter(
                $this->helper,
                $table,
                $tableWithAlias,
                $this->em,
                $this->clauseContext,
                ...$columns,
            );

            // Initialize the parameter with current state
            if ($this->helper && method_exists($parameter, 'initializeWithDependencies')) {
                $parameter->initializeWithDependencies($this->helper, $this->state);
            }

            if ($customAlias) {
                $parameter->setCustomAlias($customAlias);
            }

            $this->add($parameter);
        }
    }

    private function extractTableFromKey(string $key): string
    {
        if (str_contains($key, '|')) {
            return explode('|', $key)[1];
        }
        return $key;
    }

    private function standardizeColumns(array $columns, bool $isMainTable): array
    {
        if (ArrayUtils::isMultidimentional($columns)) {
            $columns = ArrayUtils::flattenArrayRecursive($columns);
        }

        // Handle CTE context
        if (empty($columns) && $this->state->statementContext === StatementType::CTE && !$isMainTable) {
            return [];
        }

        return empty($columns) ? ['*'] : $columns;
    }

    private function dispatchColumns(array $selectMap): array
    {
        $dispatched = [];
        $physicalToLogical = [];

        foreach ($selectMap as $logicalKey => $config) {
            $physicalName = $this->helper->getPhysicalTable($logicalKey);
            $physicalToLogical[$physicalName] = $logicalKey;

            $dispatched[$logicalKey] = $config;
            $dispatched[$logicalKey]['columns'] = [];
        }

        foreach ($selectMap as $sourceLogicalKey => $config) {
            foreach ($config['columns'] as $columnStr) {
                if (str_contains($columnStr, '.')) {
                    [$prefix, $colName] = explode('.', $columnStr, 2);

                    if (isset($physicalToLogical[$prefix])) {
                        $targetKey = $physicalToLogical[$prefix];
                        $dispatched[$targetKey]['columns'][] = $targetKey . '.' . $colName;
                    } else {
                        $dispatched[$sourceLogicalKey]['columns'][] = $sourceLogicalKey . '.' . $colName;
                    }
                } else {
                    $dispatched[$sourceLogicalKey]['columns'][] = $sourceLogicalKey . '.' . $columnStr;
                }
            }
        }
        return $dispatched;
    }
}