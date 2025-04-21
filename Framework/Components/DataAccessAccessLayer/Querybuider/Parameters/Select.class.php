<?php

declare(strict_types=1);

class Select extends MainQuery
{
    private array|string|null $columns;

    public function __construct(EntityManagerInterface $em, array|string|null ...$columns)
    {
        $this->columns = $columns;
        $this->em = $em;
    }

    public function getSql(): array
    {
        $tblh = $this->em->getTableAliasHelper();
        $columns = $this->columns;
        if (ArrayUtils::isMultidimentional($columns)) {
            $columns = ArrayUtils::flattenArrayRecursive($this->columns);
        }
        $newColumns = '';
        foreach ($columns as $key => $column) {
            $end = $key !== array_key_last($columns) ? ', ' : '';
            list($table, $alias) = $tblh->get($this->table, $this->tableAlias, $this->aliasCheck);
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
            str_contains(strtolower($column), '(') && str_contains(strtolower($column), ')') => $this->functionColumn($column, $alias),
            str_contains(strtolower($column), 'count') => $this->countColumn($column, $alias),

            default => $alias . '.' . $column
        };
    }

    private function emptyColumn(string $alias) : string
    {
        if ($this->method === 'select') {
            return $alias . '.' . '*';
        } else {
            return '';
        }
    }

    private function countColumn(string $column, string $alias) : string
    {
        list($AS, $column) = $this->as($column);
        if (str_contains(strtolower($column), '(') && str_contains(strtolower($column), ')')) {
            preg_match('#\((.*?)\)#', $column, $newColumn);
            return 'COUNT(' . $alias . '.' . $newColumn[1] . ')';
        }
        return 'COUNT(*) AS,' . (empty($AS) ? 'num' : $AS);
    }

    private function functionColumn(string $column, string $alias) : string
    {
        list($AS, $column) = $this->as($column);
        $parts = explode('(', $column);
        $function = $parts[0];
        preg_match('#\((.*?)\)#', $column, $match);
        $newColumn = $match[1];
        return strtoupper($function) . '(' . $alias . '.' . $newColumn . ')' . $AS;
    }

    private function as(string $column) : array
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