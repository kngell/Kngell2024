<?php

declare(strict_types=1);

final class TablesAliasHelper
{
    private array $tables = [];
    private array $conditionIndex = [];
    private array $logicalToPhysicalMap = [];
    private array $joinedMap = [];
    private array $nestedRelationships = [];
    private array $aliasParentMap = [];
    private ?string $table = null;
    private ?string $joinContext = null;
    private bool $isJoincontext = false;
    private ?string $fromTable = null;
    private ?string $toTable = null;
    private string $quoteChar = '`';
    private ?string $customAlias = null;

    public function __construct(private Token $token)
    {
    }

    public function reset(): void
    {
        $this->tables = [];
        $this->conditionIndex = [];
        $this->logicalToPhysicalMap = [];
        $this->nestedRelationships = [];
        $this->aliasParentMap = [];
        $this->joinedMap = [];
        $this->table = null;
        $this->fromTable = null;
        $this->toTable = null;
        $this->joinContext = null;
        $this->isJoincontext = false;
    }

    public function get(string $tbl, array &$tableAlias, array &$aliasCheck): array
    {
        if (empty($tbl)) {
            throw new InvalidArgumentException('Table name cannot be empty in TablesAliasHelper::get()');
        }
        if (!empty($this->customAlias)) {
            $physicalTable = $this->getPhysicalTable($tbl);
            $alias = $this->customAlias;

            $tableAlias[$tbl] = $alias;
            if (!in_array($alias, $aliasCheck, true)) {
                $aliasCheck[] = $alias;
            }
            $this->customAlias = null;
            return [$physicalTable, $alias];
        }

        if ($this->joinContext && $this->getPhysicalTable($this->joinContext) === $tbl) {
            if (array_key_exists($this->joinContext, $tableAlias)) {
                $physicalTable = $this->getPhysicalTable($this->joinContext);
                return [$physicalTable, $tableAlias[$this->joinContext]];
            }
        }

        if (array_key_exists($tbl, $tableAlias)) {
            $physicalTable = $this->getPhysicalTable($tbl);
            return [$physicalTable, $tableAlias[$tbl]];
        }

        $physicalTable = $this->getPhysicalTable($tbl);
        $baseAlias = $this->generateBaseAlias($tbl, $physicalTable, $tableAlias);
        $alias = $baseAlias;

        if ($this->isJoincontext) {
            $alias = $this->generateJoinAlias($baseAlias, $aliasCheck, $tbl);
            $this->trackNestedRelationship($tbl, $physicalTable);
        } elseif (isset($tableAlias[$physicalTable])) {
            $alias = $tableAlias[$physicalTable];
        }

        $alias = $this->ensureAliasUnique($alias, $aliasCheck);

        $tableAlias[$tbl] = $alias;

        if ($this->isJoincontext && $this->table) {
            $this->joinedMap[$this->table][$tbl] = $physicalTable;
            $this->aliasParentMap[$alias] = [$this->table, $tbl];
        }

        if (!in_array($alias, $aliasCheck, true)) {
            $aliasCheck[] = $alias;
        }

        return [$physicalTable, $alias];
    }

    public function mapTableColumn(string|int $logicalTable, int $position = 0): array
    {
        if (is_string($logicalTable)) {
            $parts = explode($this->separator($logicalTable), $logicalTable);

            if (count($parts) === 2) {
                return [$parts[0], $parts[1]];
            }
            if (count($parts) === 1) {
                $column = $parts[0];
                $default = ($position === 0 && $this->fromTable)
                           ? $this->fromTable
                           : ($this->toTable ?? $this->getDefaultTable());

                return [$default, $column];
            }
        }

        return [$this->getDefaultTable(), (string) $logicalTable];
    }

    public function resolveColumn(string $columnStr, QueryState $state): string
    {
        if (!str_contains($columnStr, '.')) {
            return $columnStr;
        }

        [$prefix, $col] = explode('.', $columnStr, 2);

        $logicalName = array_search($prefix, $state->logicalToPhysicalMap);

        if ($logicalName) {
            return $logicalName . '.' . $col;
        }

        return $columnStr;
    }

    public function extractColumnName(null|string $condition): string
    {
        if (is_null($condition)) {
            return '';
        }
        $separator = $this->separator($condition);
        $parts = explode($separator, $condition);
        return count($parts) === 2 ? $parts[1] : $parts[0];
    }

    public function generateUniqueParameterName(string $baseName, array $existingParameters): string
    {
        $counter = 1;
        $name = $this->normalizeParameterName($baseName);

        while (array_key_exists($name, $existingParameters)) {
            $name = $this->normalizeParameterName($baseName . '_' . $counter);
            $counter++;
        }

        return $name;
    }

    public function separator(string $str): string
    {
        return strpos($str, '|') !== false ? '|' : '.';
    }

    public function getLogicalToPhysicalMap(): array
    {
        return $this->logicalToPhysicalMap;
    }

    public function setLogicalToPhysicalMap(array $map): self
    {
        $this->logicalToPhysicalMap = $map;
        return $this;
    }

    public function setTable(?string $table): self
    {
        $this->table = $table;
        return $this;
    }

    public function setJoinContext(?string $joinContext): self
    {
        $this->joinContext = $joinContext;
        return $this;
    }

    public function setJoinMapping(?string $from, ?string $to): void
    {
        $this->fromTable = $from;
        $this->toTable = $to;
    }

    public function setTables(array $tables): self
    {
        $this->tables = $tables;
        return $this;
    }

    public function setConditionIndex(array $conditionIndex): self
    {
        $this->conditionIndex = $conditionIndex;
        return $this;
    }

    public function getToken(): Token
    {
        return $this->token;
    }

    public function getJoinedMap(): array
    {
        return $this->joinedMap;
    }

    public function addJoinedRelation(string $fromTable, string $toTable): self
    {
        $this->joinedMap[$fromTable][] = $toTable;
        return $this;
    }

    public function hasJoined(string $fromTable, string $toTable): bool
    {
        return isset($this->joinedMap[$fromTable]) &&
               in_array($toTable, $this->joinedMap[$fromTable], true);
    }

    public function isJoincontext(bool $isJoincontext = true): TablesAliasHelper
    {
        $this->isJoincontext = $isJoincontext;
        return $this;
    }

    public function getNestedRelationships(): array
    {
        return $this->nestedRelationships;
    }

    public function getAliasParentMap(): array
    {
        return $this->aliasParentMap;
    }

    public function getParentOfAlias(string $alias): ?array
    {
        return $this->aliasParentMap[$alias] ?? null;
    }

    public function isNestedRelationship(string $tableName): bool
    {
        return str_contains($tableName, '.');
    }

    public function extractParentTable(string $tableName): string
    {
        if (str_contains($tableName, '.')) {
            return explode('.', $tableName, 2)[0];
        }
        return $tableName;
    }

    public function extractChildTable(string $tableName): string
    {
        if (str_contains($tableName, '.')) {
            return explode('.', $tableName, 2)[1];
        }
        return $tableName;
    }

    public function getPhysicalTable(string $logicalTable): string
    {
        if (str_contains($logicalTable, '.')) {
            $parts = explode('.', $logicalTable);
            $targetTable = end($parts);
        } else {
            $targetTable = $logicalTable;
        }

        $table = str_replace('_join_', '', $targetTable);

        if (str_contains($table, '_logical_')) {
            return explode('_logical_', $table)[0];
        }

        return $table;
    }
    // private function getDefaultTable(): string
    // {
    //     if (empty($this->tables)) {
    //         throw new RuntimeException('No tables available for alias generation. Tables must be set before generating aliases.');
    //     }

    //     $defaultTable = array_key_first($this->tables);

    //     if (empty($defaultTable)) {
    //         throw new RuntimeException('Default table cannot be empty. Check table configuration.');
    //     }

    //     return $defaultTable;
    // }
    /**
     * @param string $quoteChar
     *
     * @return TablesAliasHelper
     */
    public function setQuoteChar(string $quoteChar): TablesAliasHelper
    {
        $this->quoteChar = $quoteChar;

        return $this;
    }

    public function quote(string $identifier): string
    {
        if ($identifier === '*') {
            return '*';
        }

        // Handle already quoted strings or dots
        if (str_contains($identifier, '.')) {
            $parts = explode('.', $identifier);
            return implode('.', array_map([$this, 'quote'], $parts));
        }

        return $this->quoteChar . str_replace($this->quoteChar, $this->quoteChar . $this->quoteChar, $identifier) . $this->quoteChar;
    }

    /**
     * @param null|string $customAlias
     *
     * @return TablesAliasHelper
     */
    public function setCustomAlias(?string $customAlias): TablesAliasHelper
    {
        $this->customAlias = $customAlias;

        return $this;
    }

    private function normalizeParameterName(string $name): string
    {
        return 'p_' . preg_replace('/[^a-zA-Z0-9_]/', '_', strtolower($name));
    }

    private function generateBaseAlias(string $tbl, string $physicalTable, array $tableAlias): string
    {
        if ($this->isNestedRelationship($tbl)) {
            $parentTable = $this->extractParentTable($tbl);
            $childTable = $this->extractChildTable($tbl);

            $parentAlias = $tableAlias[$parentTable] ?? $this->getFirstchar($parentTable);
            return $tableAlias[$childTable] ?? $this->getFirstchar($childTable);
        }

        return $tableAlias[$physicalTable] ?? $this->getFirstchar($physicalTable);
    }

    private function getFirstchar(string $str): string
    {
        $length = strlen($str);

        for ($i = 0; $i < $length; $i++) {
            $char = $str[$i];

            if (preg_match('/[a-zA-Z0-9_]/', $char)) {
                return strtolower($char);
            }
        }

        throw new InvalidArgumentException(
            "No valid alias character found in string: {$str}",
        );
    }

    private function generateJoinAlias(string $baseAlias, array $aliasCheck, string $tbl): string
    {
        $parentTable = null;
        if ($this->isNestedRelationship($tbl)) {
            $parentTable = $this->extractParentTable($tbl);
        }

        $maxCounter = 1;

        foreach ($aliasCheck as $existingAlias) {
            if (
                str_starts_with($existingAlias, $baseAlias)
                && preg_match('/\d+$/', $existingAlias, $matches)
            ) {
                $maxCounter = max($maxCounter, (int) $matches[0]);
            }
        }

        return $baseAlias . ($maxCounter + 1);
    }

    private function ensureAliasUnique(string $alias, array $aliasCheck): string
    {
        $originalAlias = $alias;
        $counter = 1;

        while (in_array($alias, $aliasCheck, true) || is_numeric($alias)) {
            $alias = $originalAlias . $counter;
            $counter++;
            if ($counter > 100) {
                $alias = $originalAlias . '_' . uniqid();
                break;
            }
        }

        return $alias;
    }

    private function trackNestedRelationship(string $tableName, string $physicalTable): void
    {
        if ($this->isNestedRelationship($tableName)) {
            $parentTable = $this->extractParentTable($tableName);
            $childTable = $this->extractChildTable($tableName);

            if (!isset($this->nestedRelationships[$parentTable])) {
                $this->nestedRelationships[$parentTable] = [];
            }

            $this->nestedRelationships[$parentTable][$childTable] = $physicalTable;
        }
    }

    private function getDefaultTable(): string
    {
        if (!empty($this->joinContext)) {
            return $this->joinContext;
        }

        if (!empty($this->tables)) {
            $defaultTable = array_key_first($this->tables);
            if (!empty($defaultTable)) {
                return $defaultTable;
            }
        }

        throw new RuntimeException('No tables available for alias generation.');
    }
}