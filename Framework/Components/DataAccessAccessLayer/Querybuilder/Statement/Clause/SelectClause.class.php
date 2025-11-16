<?php

declare(strict_types=1);

class SelectClause extends SqlQuery implements ClauseComponentInterface
{
    private const SqlClause CLAUSE = SqlClause::SELECT;

    public function __construct(
        private array $columnMap,
        bool $withAlias,
        ?TablesAliasHelper $helper = null,
    ) {
        parent::__construct(self::CLAUSE);
        $this->withAlias = $withAlias;
        $this->helper = $helper;
        $this->children = new Collection();
        // Don't initialize columns here - wait until we have state
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

    public function isComposite(): bool
    {
        return true;
    }

    private function initializeColumns(): void
    {
        foreach ($this->columnMap as $key => $config) {
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