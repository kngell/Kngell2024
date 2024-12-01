<?php

declare(strict_types=1);

class Select extends MainQuery
{
    private array|string|null $columns;

    public function __construct(TablesAliasHelper $tblh, array|string|null ...$columns)
    {
        $this->columns = $columns;
        $this->tblh = $tblh;
    }

    public function getSql(): array
    {
        $columns = $this->columns;
        if (ArrayUtils::isMultidimentional($columns)) {
            $columns = ArrayUtils::flattenArrayRecursive($this->columns);
        }
        $newColumns = '';
        foreach ($columns as $key => $column) {
            $end = $key !== array_key_last($columns) ? ', ' : '';
            list($table, $alias) = $this->tblh->get($this->table, $this->tableAlias, $this->aliasCheck);
            $newColumns .= $this->column($column, $alias) . $end;
        }
        return [
            $newColumns,
            $this->tableAlias,
            $this->aliasCheck,
            $this->parameters,
            $this->bind_arr,
        ];
    }

    private function column(array|string $column, string $alias) : string
    {
        return match (true) {
            is_array($column) && empty($column) => $this->emptyColumn($alias),
            str_contains(strtolower($column), 'count') => $this->countColumn($column, $alias),
            str_contains(strtolower($column), '(') && str_contains(strtolower($column), ')') => $this->functionColumn($column, $alias),
            default => $alias . '.' . $column
        };
    }

    private function emptyColumn(string $alias) : string
    {
        if ($this->method === 'select') {
            return $alias . '.' . '*, ';
        } else {
            return '';
        }
    }

    private function countColumn(string $column, string $alias) : string
    {
        if (str_contains(strtolower($column), '(') && str_contains(strtolower($column), ')')) {
            preg_match('#\((.*?)\)#', $column, $newColumn);
            return 'COUNT(' . $alias . '.' . $newColumn[1] . ')';
        }
        return 'COUNT(' . $alias . '.*),';
    }

    private function functionColumn(string $column, string $alias) : string
    {
        $parts = explode('(', $column);
        $function = $parts[0];
        preg_match('#\((.*?)\)#', $column, $match);
        $newColumn = $match[1];
        return strtoupper($function) . '(' . $alias . '.' . $newColumn . ')';
    }
}