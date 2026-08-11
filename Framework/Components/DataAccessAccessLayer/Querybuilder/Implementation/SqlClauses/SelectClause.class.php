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
        $this->distinctCount = $columnsCollector->getDistinctCount();
        $this->customAlias = $columnsCollector->getCustomAlias();
    }

    public function build(): string
    {
        if (!$this->helper) {
            throw new RuntimeException('TablesAliasHelper not initialized');
        }
        if ($this->distinctCount) {
            $stop = true;
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
        if ($this->distinct || $this->state->distinct) {
            return 'DISTINCT';
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
            $distinctCount = $config['distinctCount'] ?? false;
            $parameter = new ColumnsParameter(
                $this->helper,
                $table,
                $tableWithAlias,
                $distinctCount,
                $this->em,
                $this->clauseContext,
                ...$columns,
            );

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
        if (empty($columns) && $this->clauseContext === StatementType::CTE && !$isMainTable) {
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
            $physicalToLogical[$physicalName][] = $logicalKey;
            $dispatched[$logicalKey] = $config;
            $dispatched[$logicalKey]['columns'] = [];
            $this->buildTableAlias($logicalKey, $physicalName);
        }

        foreach ($selectMap as $sourceLogicalKey => $config) {
            foreach ($config['columns'] as $columnStr) {
                if ($columnStr instanceof SqlComponent) {
                    $child = $columnStr;
                    $this->prepareChild($child);
                    $columnStr = $child->build();
                    $this->mergeChildState($child);
                }
                if (str_contains($columnStr, '(') || str_contains($columnStr, ' ')) {
                    $normalizedExpression = $columnStr;
                    $translated = false;
                    foreach ($physicalToLogical as $physicalName => $logicalKeys) {
                        $pattern = '/\b' . preg_quote($physicalName, '/') . '\./i';

                        if (preg_match($pattern, $columnStr)) {
                            $targetKey = in_array($sourceLogicalKey, $logicalKeys) ? $sourceLogicalKey : $logicalKeys[0];
                            $normalizedExpression = preg_replace($pattern, $targetKey . '.', $normalizedExpression);
                            $translated = true;
                        }
                    }
                    if ($translated) {
                        $dispatched[$sourceLogicalKey]['columns'][] = $normalizedExpression;
                    } else {
                        $dispatched[$sourceLogicalKey]['columns'][] = $sourceLogicalKey . '.' . $columnStr;
                    }
                    continue;
                }
                if (str_contains($columnStr, '.')) {
                    [$prefix, $colName] = explode('.', $columnStr, 2);
                    if (isset($physicalToLogical[$prefix])) {
                        $targetKeys = $physicalToLogical[$prefix];
                        $targetKey = in_array($sourceLogicalKey, $targetKeys) ? $sourceLogicalKey : $targetKeys[0];

                        $dispatched[$targetKey]['columns'][] = $targetKey . '.' . $colName;
                    } else {
                        $dispatched[$sourceLogicalKey]['columns'][] = $sourceLogicalKey . '.' . $colName;
                    }
                } else {
                    $dispatched[$sourceLogicalKey]['columns'][] = $sourceLogicalKey . '.' . $columnStr;
                }
            }
        }
        return $this->reorderDispatchedByQueryMap($dispatched);
    }

    private function reorderDispatchedByQueryMap(array $dispatched): array
    {
        $reordered = [];

        $logicalKeyMap = [];
        foreach (array_keys($dispatched) as $logicalKey) {
            $baseName = preg_replace('/_logical_\d+$/', '', $logicalKey);
            $logicalKeyMap[$baseName][] = $logicalKey;
        }

        foreach ($this->queryMap as $queryKey) {
            if (isset($dispatched[$queryKey])) {
                $reordered[$queryKey] = $dispatched[$queryKey];
                continue;
            }

            if (isset($logicalKeyMap[$queryKey])) {
                foreach ($logicalKeyMap[$queryKey] as $logicalKey) {
                    if (isset($dispatched[$logicalKey]) && !isset($reordered[$logicalKey])) {
                        $reordered[$logicalKey] = $dispatched[$logicalKey];
                    }
                }
            }
        }

        foreach ($dispatched as $logicalKey => $config) {
            if (!isset($reordered[$logicalKey])) {
                $reordered[$logicalKey] = $config;
            }
        }

        return $reordered;
    }

    private function buildTableAlias(string $logicalKey, string $physicalName): void
    {
        $this->helper->get($logicalKey, $this->state->tableAlias, $this->state->aliasCheck);
        $this->state = $this->state->withLogicalToPhysicalMap(
            array_merge($this->state->logicalToPhysicalMap, [$logicalKey => $physicalName]),
        );
    }
}