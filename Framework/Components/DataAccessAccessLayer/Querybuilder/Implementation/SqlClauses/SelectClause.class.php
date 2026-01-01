<?php

declare(strict_types=1);

class SelectClause extends SqlQuery implements RegularClauseComponentInterface
{
    private const SqlClause CLAUSE = SqlClause::SELECT;

    public function __construct(
        private array $columnMap,
        bool $withAlias,
        bool $distinct,
        ?TablesAliasHelper $helper = null,
    ) {
        parent::__construct(self::CLAUSE);
        $this->withAlias = $withAlias;
        $this->distinct = $distinct;
        $this->helper = $helper;
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
        foreach ($this->columnMap as $key => $config) {
            if (array_key_first($this->columnMap) !== $key && empty($config['columns'])) {
                continue;
            }
            $table = $config['table'] ?? $this->extractTableFromKey($key);
            $columns = $this->standardizeColumns($config['columns'] ?? []);
            $customAlias = $config['customAlias'] ?? null;
            $tableWithAlias = $config['withAlias'] ?? $this->withAlias;

            if (empty($columns)) {
                continue;
            }

            $parameter = new ColumnsParameter($this->helper, $table, $tableWithAlias, ...$columns);

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

    private function standardizeColumns(array $columns): array
    {
        if (ArrayUtils::isMultidimentional($columns)) {
            $columns = ArrayUtils::flattenArrayRecursive($columns);
        }

        return empty($columns) ? ['*'] : $columns;
    }
}