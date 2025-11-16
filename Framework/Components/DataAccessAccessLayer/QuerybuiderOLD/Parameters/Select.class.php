<?php

declare(strict_types=1);

class Select extends MainQuery
{
    private array|string|null $columns;
    private bool $selectAsAlias = false;
    private array $customAliases = [];

    public function __construct(EntityManagerInterface $em, bool $selectAsAlias, array|string|null ...$columns)
    {
        $this->columns = $columns;
        $this->em = $em;
        $this->selectAsAlias = $selectAsAlias;
    }

    public function getSql(): array
    {
        $tblh = $this->em->getTableAliasHelper();
        $columns = $this->standardizeColumns($this->columns);
        $columnStrings = [];

        $logicalKey = $this->joinContext;
        if ($logicalKey === null) {
            $logicalKey = $this->table;
        }

        // Determine physical table and build the mapping
        if (str_contains($logicalKey, '_join_')) {
            $physicalTable = explode('_join_', $logicalKey, 2)[0];
        } else {
            $physicalTable = $logicalKey;
        }

        // Build the logical-to-physical mapping
        $this->logicalToPhysicalMap[$logicalKey] = $physicalTable;

        foreach ($columns as $key => $column) {
            list($table, $alias) = $tblh->get($logicalKey, $this->tableAlias, $this->aliasCheck);
            $columnStrings[] = $this->column($column, $alias, $tblh);
        }

        $newColumns = implode(', ', $columnStrings);

        return [
            $newColumns,
            $this->tableAlias,
            $this->aliasCheck,
            $this->parameters,
            $this->bind_arr,
        ];
    }

    public function setCustomAlias(string $column, string $alias): self
    {
        $this->customAliases[$column] = $alias;
        return $this;
    }

    private function standardizeColumns(array $columns): array
    {
        $standardizedColumn = $columns;
        if (ArrayUtils::isMultidimentional($standardizedColumn)) {
            $standardizedColumn = ArrayUtils::flattenArrayRecursive($columns);
            if (empty($standardizedColumn)) {
                $standardizedColumn = ['*'];
            }
        }
        if (ArrayUtils::isMultidimentional($standardizedColumn)) {
            return $this->standardizeColumns($standardizedColumn);
        }
        return $standardizedColumn;
    }

    private function column(array|string $column, string $alias, TablesAliasHelper $tblh): string
    {
        // Skip aliasing if column already has an alias or is a complex expression
        if (is_string($column) && $this->shouldSkipAliasing($column)) {
            return $this->handleComplexColumn($column, $alias, $tblh);
        }

        return match (true) {
            is_array($column) && empty($column) => $this->emptyColumn($alias),
            str_contains(strtolower($column), '(') && str_contains(strtolower($column), ')') => $this->functionColumn($column, $alias),
            str_contains(strtolower($column), 'count') => $this->countColumn($column, $alias),
            $this->selectAsAlias => $this->aliasColumn($column, $alias),
            default => $alias . '.' . $column
        };
    }

    private function shouldSkipAliasing(string $column): bool
    {
        $lowerColumn = strtolower($column);

        // Skip if already has AS alias
        if (str_contains($lowerColumn, ' as ')) {
            return true;
        }

        // Skip if it's a complex expression with operators
        if (preg_match('/[+\-*\/()<>!=]/', $column)) {
            return true;
        }

        // Skip if it's a subquery
        if (str_contains($lowerColumn, 'select ') && str_contains($lowerColumn, ' from ')) {
            return true;
        }

        // Skip if it's a database function
        if (preg_match('/[a-z_]+\([^)]*\)/i', $column)) {
            return true;
        }

        return false;
    }

    private function handleComplexColumn(string $column, string $alias, TablesAliasHelper $tblh): string
    {
        // If it's a simple column reference with table prefix, keep it as is
        if ($this->isQualifiedColumn($column, $tblh)) {
            return $column;
        }

        // For other complex expressions, just return as is
        return $column;
    }

    private function isQualifiedColumn(string $column, TablesAliasHelper $tblh): bool
    {
        $separator = $tblh->separator($column);
        $parts = explode($separator, $column);
        return count($parts) === 2 && !empty($parts[0]) && !empty($parts[1]);
    }

    private function aliasColumn(string $column, string $alias): string
    {
        // Validate column name to prevent SQL injection
        if (!$this->isValidColumnName($column)) {
            throw new InvalidArgumentException("Invalid column name: {$column}");
        }

        if ($column === '*') {
            return $alias . '.' . $column;
        }

        // Use custom alias if specified
        $finalAlias = $this->customAliases[$column] ?? $alias . '_' . $column;

        return $alias . '.' . $column . ' AS ' . $finalAlias;
    }

    /**
     * Basic column name validation.
     */
    private function isValidColumnName(string $column): bool
    {
        // Allow alphanumeric, underscores, and wildcard
        return preg_match('/^[a-zA-Z0-9_*]+$/', $column) === 1;
    }

    /**
     * Quote column names if they contain reserved keywords or special characters.
     */
    private function quoteColumn(string $column): string
    {
        $reservedKeywords = ['order', 'group', 'select', 'where', 'from', 'join', 'as'];

        if (in_array(strtolower($column), $reservedKeywords) ||
            preg_match('/[^a-zA-Z0-9_]/', str_replace('*', '', $column))) {
            return "`{$column}`";
        }

        return $column;
    }

    private function emptyColumn(string $alias): string
    {
        if ($this->method === 'select') {
            return $alias . '.' . '*'; // table.* for empty select
        } else {
            return '';
        }
    }

    private function countColumn(string $column, string $alias): string
    {
        list($AS, $column) = $this->as($column);

        $lowerColumn = strtolower(trim($column));
        if (str_starts_with($lowerColumn, 'count(') && str_contains($lowerColumn, ')')) {
            preg_match('#\((.*?)\)#', $column, $newColumn);
            return 'COUNT(' . $alias . '.' . $newColumn[1] . ')';
        }

        return $this->aliasColumn($column, $alias);
    }

    private function functionColumn(string $column, string $alias): string
    {
        list($AS, $column) = $this->as($column);
        $parts = explode('(', $column);
        $function = $parts[0];

        preg_match('#\((.*?)\)#', $column, $match);
        $newColumn = $match[1];

        // Handle table-qualified columns in functions
        if (!empty($alias) && $this->isSimpleColumn($newColumn)) {
            $newColumn = $alias . '.' . $newColumn;
        }

        $result = strtoupper($function) . '(' . $newColumn . ')';

        // Add AS alias for functions when in selectAsAlias mode
        if ($this->selectAsAlias && empty($AS)) {
            $AS = $alias . '_' . $function . '_' . str_replace(['.', '(', ')'], '_', $newColumn);
        }

        return $result . (!empty($AS) ? ' AS ' . $AS : '');
    }

    /**
     * Check if string is a simple column name (not an expression).
     */
    private function isSimpleColumn(string $value): bool
    {
        return preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $value) === 1;
    }

    private function as(string $column): array
    {
        $AS = '';
        $parts = explode('as', strtolower($column));
        if (count($parts) === 2) {
            $AS = trim($parts[1]);
            $column = trim($parts[0]);
        }
        return [$AS, $column];
    }
}
