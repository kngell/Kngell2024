<?php

declare(strict_types=1);
final class TablesAliasHelper
{
    private array $tables;
    private array $conditionIndex;

    public function __construct(private Token $token)
    {
    }

    public function get(string $tbl, array &$tableAlias, array &$aliasCheck) : array
    {
        $alias = $this->getTableAndAlias($tbl, $tableAlias);
        if (! $alias) {
            $alias = '';
            $t = '';
            if ($tbl !== null && ! empty($tbl)) {
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

    public function keyColumns(array $condition) : array
    {
        if (array_key_last($condition) === 'operator') {
            unset($condition['operator']);
        }
        return array_values($condition);
    }

    public function mapTableColumn(string $str, array $tables = []) : array
    {
        $parts = explode('.', $str);
        if (count($parts) === 2) {
            $tableColumn = $parts[0];
            $column = $parts[1];
        } elseif (count($parts) === 1) {
            $column = $parts[0];
        }
        $tables = [];
        foreach ($this->tables as $table => $columns) {
            $columns = ArrayUtils::flattenArrayRecursive($columns);
            if (in_array($column, $columns)) {
                $tables[] = $table;
            }
        }
        if (isset($tableColumn)) {
            return[$tableColumn, $column];
        }

        if (count($tables) === 0) {
            return ['', $column];
            // throw new BadQueryRequestException("The column $column does not represent any specified table");
        }
        if (count($tables) > 1) {
            throw new BadQueryRequestException("The column $column represents multiples tables");
        }
        return [$tables[0], $column];
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

    protected function getTableAndAlias(string $table, $tableAlias) : string|bool
    {
        foreach ($tableAlias as $tbl => $alias) {
            if ($table == $tbl) {
                return $alias;
            }
        }
        return false;
    }
}
