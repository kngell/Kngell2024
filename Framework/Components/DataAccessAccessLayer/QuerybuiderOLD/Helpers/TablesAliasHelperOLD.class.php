<?php

declare(strict_types=1);
final class TablesAliasHelperOLD
{
    private const string PARAM_SUFFIX = 'azertyuiopmlkjhgfdsq';

    private array $tables;
    private array $conditionIndex;

    public function __construct(private Token $token)
    {
    }

    public function get(string $tbl, array &$tableAlias, array &$aliasCheck): array
    {
        $alias = $this->getTableAndAlias($tbl, $tableAlias);
        if (!$alias) {
            $alias = '';
            $t = '';
            if ($tbl !== null && !empty($tbl)) {
                $parts = explode('|', $tbl);
                if (count($parts) == 1) {
                    $alias = strtolower($tbl[0]);
                    $t = $tbl;
                } elseif (count($parts) == 2) {
                    $alias = $parts[1];
                    $t = trim($parts[0]);
                }
                while (in_array($alias, $aliasCheck) || is_numeric($alias)) {
                    $alias = $this->token->generate(1, $tbl);
                }
                array_push($aliasCheck, $alias);
                $tableAlias[$t] = $alias;
                $tbl = $t;
            }
        }
        return [$tbl, $alias];
    }

    public function keyColumns(array $condition): array
    {
        if (array_key_last($condition) === 'operator') {
            unset($condition['operator']);
        }
        return array_values($condition);
    }

    public function mapTableColumn(string|int $str, array $tables = []): array
    {
        if (is_string($str)) {
            $separator = $this->separator($str);
            $parts = explode($separator, $str);

            if (count($parts) === 2) {
                // Table and column specified: "table.column" or "table|column"
                $tableColumn = $parts[0];
                $column = $parts[1];
                return [$tableColumn, $column];
            }
            if (count($parts) === 1) {
                // Only column specified: "column" - use default table
                $column = $parts[0];
                $defaultTable = $this->getDefaultTable();
                return [$defaultTable, $column];
            }
        } else {
            // Non-string (int, etc) - use default table
            $column = $str;
            $defaultTable = $this->getDefaultTable();
            return [$defaultTable, $column];
        }

        return ['', $str];
    }

    public function generateUniqueParameterName(string $leftCondition, array $parameters): string
    {
        // 1. Get the base column name.
        $columnName = $this->extractColumnName($leftCondition);

        // 2. Loop until a unique name is generated.
        do {
            // Generate a random token suffix
            $token = $this->token->generate(2, self::PARAM_SUFFIX);
            $paramName = $token . '_' . $columnName;
        } while (array_key_exists($paramName, $parameters));

        return $paramName;
    }

    public function extractColumnName(string $condition): string
    {
        $separator = $this->separator($condition);
        $parts = explode($separator, $condition);

        // If 'table.column' or 'table|column', return 'column'; otherwise return the whole string.
        return count($parts) === 2 ? $parts[1] : $parts[0];
    }

    /**
     * Get the value of token.
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Set the value of tables.
     *
     * @param array $tables
     *
     * @return self
     */
    public function setTables(array $tables): self
    {
        $this->tables = $tables;

        return $this;
    }

    /**
     * Set the value of conditionIndex.
     *
     * @param array $conditionIndex
     *
     * @return self
     */
    public function setConditionIndex(array $conditionIndex): self
    {
        $this->conditionIndex = $conditionIndex;

        return $this;
    }

    public function separator(string $str): string
    {
        return strpos($str, '|') !== false ? '|' : '.';
    }

    protected function getTableAndAlias(string $table, $tableAlias): string|bool
    {
        foreach ($tableAlias as $tbl => $alias) {
            if ($table == $tbl) {
                return $alias;
            }
        }
        return false;
    }

    /**
     * Get the default/main table from the tables array.
     */
    private function getDefaultTable(): string
    {
        if (empty($this->tables)) {
            return '';
        }

        // Return the first table as default (usually the main FROM table)
        return array_key_first($this->tables);
    }
}