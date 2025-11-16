<?php

declare(strict_types=1);

final class TablesAliasHelper
{
    private array $tables = [];
    private array $conditionIndex = [];
    private array $logicalToPhysicalMap = [];
    private array $joinedMap = []; // 🆕 logical_table => joined_table(s)
    private ?string $table = null;
    private ?string $joinContext = null;

    public function __construct(private Token $token)
    {
    }

    // ------------------------------------------------------------
    // 🧠  ALIAS RESOLUTION
    // ------------------------------------------------------------
    public function get(string $tbl, array &$tableAlias, array &$aliasCheck): array
    {
        if (empty($tbl)) {
            throw new InvalidArgumentException('Table name cannot be empty in TablesAliasHelper::get()');
        }

        // if (empty($this->tables)) {
        //  throw new RuntimeException('Tables must be initialized before generating aliases. Call setTables() first.');
        // }
        if ($this->joinContext && $this->getPhysicalTable($this->joinContext) === $tbl) {
            if (array_key_exists($this->joinContext, $tableAlias)) {
                $physicalTable = $this->getPhysicalTable($this->joinContext);
                return [$physicalTable, $tableAlias[$this->joinContext]];
            }
        }

        // Then check for the table directly
        if (array_key_exists($tbl, $tableAlias)) {
            $physicalTable = $this->getPhysicalTable($tbl);
            return [$physicalTable, $tableAlias[$tbl]];
        }
        // 🔹 Step 1: Handle existing alias
        if (array_key_exists($tbl, $tableAlias)) {
            return [$this->getPhysicalTable($tbl), $tableAlias[$tbl]];
        }

        $physicalTable = $this->getPhysicalTable($tbl);
        $isJoinContext = str_contains($tbl, '_join_');
        $baseAlias = $tableAlias[$physicalTable] ?? strtolower($physicalTable[0]);
        $alias = $baseAlias;

        // 🔹 Step 2: Handle JOINed tables (create numbered alias)
        if ($isJoinContext) {
            $alias = $this->generateJoinAlias($baseAlias, $aliasCheck);
        } elseif (isset($tableAlias[$physicalTable])) {
            // If base alias already exists and not a forced join, reuse
            $alias = $tableAlias[$physicalTable];
        }

        // 🔹 Step 3: Guarantee uniqueness
        $alias = $this->ensureAliasUnique($alias, $aliasCheck);

        // 🔹 Step 4: Save alias and relationships
        if ($isJoinContext) {
            $tableAlias[$tbl] = $alias;
            $this->joinedMap[$this->table][$tbl] = $physicalTable; // Track join relation
        } else {
            $tableAlias[$physicalTable] = $alias;
        }

        if (!in_array($alias, $aliasCheck, true)) {
            $aliasCheck[] = $alias;
        }

        return [$physicalTable, $alias];
    }

    // ------------------------------------------------------------
    // 🧭  TABLE + COLUMN MAPPING
    // ------------------------------------------------------------
    public function mapTableColumn(string|int $str, array $tables = []): array
    {
        if (is_string($str)) {
            $separator = $this->separator($str);
            $parts = explode($separator, $str);

            if (count($parts) === 2) {
                // 🎯 FIX: For explicit table.column references, use as-is
                $tableColumn = $parts[0];
                $column = $parts[1];
                return [$tableColumn, $column];
            }

            if (count($parts) === 1) {
                $column = $parts[0];
                // 🎯 FIX: For unqualified columns, use the DEFAULT table (product), not join context
                $defaultTable = $this->getDefaultTable();
                return [$defaultTable, $column];
            }
        }

        $defaultTable = $this->getDefaultTable();
        return [$defaultTable, (string) $str];
    }

    // ------------------------------------------------------------
    // 🧱  UTILITIES
    // ------------------------------------------------------------
    public function extractColumnName(string $condition): string
    {
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
    // public function generateUniqueParameterName(string $leftCondition, array $parameters): string
    // {
    //     $columnName = $this->extractColumnName($leftCondition);
    //     $counter = 1;

    //     do {
    //         $paramName = 'p' . $counter . '_' . $columnName;
    //         $counter++;
    //     } while (array_key_exists($paramName, $parameters) && $counter < 1000);

    //     return $paramName;
    // }

    public function separator(string $str): string
    {
        return strpos($str, '|') !== false ? '|' : '.';
    }

    // ------------------------------------------------------------
    // 🧩  LOGICAL / PHYSICAL MAPS
    // ------------------------------------------------------------
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

    public function setTables(array $tables): self
    {
        // FIX: Removed the check below. The responsibility to enforce tables
        // existing belongs to the usage methods (like getDefaultTable()), not the setter.
        // if (empty($tables)) {
        //     throw new InvalidArgumentException('Tables array cannot be empty');
        // }
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

    // ------------------------------------------------------------
    // 🆕 JOINED MAP ACCESSORS
    // ------------------------------------------------------------
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

    private function normalizeParameterName(string $name): string
    {
        // Convert to lowercase and replace non-alphanumeric with underscores
        return 'p_' . preg_replace('/[^a-zA-Z0-9_]/', '_', strtolower($name));
    }

    private function getPhysicalTable(string $tbl): string
    {
        if (str_contains($tbl, '_join_')) {
            return explode('_join_', $tbl, 2)[0];
        }
        return $tbl;
    }

    private function generateJoinAlias(string $baseAlias, array $aliasCheck): string
    {
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

    private function getDefaultTable(): string
    {
        if (empty($this->tables)) {
            // This is the correct place to throw an exception when the tables array is unexpectedly empty,
            // as the function is trying to USE the table list.
            throw new RuntimeException('No tables available for alias generation. Tables must be set before generating aliases.');
        }

        $defaultTable = array_key_first($this->tables);

        if (empty($defaultTable)) {
            throw new RuntimeException('Default table cannot be empty. Check table configuration.');
        }

        return $defaultTable;
    }
}