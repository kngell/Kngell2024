<?php

declare(strict_types=1);
class GroupBy extends MainQuery
{
    private array|string|null  $columns;

    public function __construct(TablesAliasHelper $tblh, array $tables, array|string|null ...$columns)
    {
        $this->columns = $columns;
        $this->tblh = $tblh->setTables($tables);
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
            list($table, $column) = $this->tblh->mapTableColumn($column);
            list($table, $alias) = $this->tblh->get($table, $this->tableAlias, $this->aliasCheck);
            $alias = ! empty($alias) ? $alias . '.' : '';
            $newColumns .= $alias . $column . $end;
        }
        return [
            $newColumns,
            $this->tableAlias,
            $this->aliasCheck,
            $this->parameters,
            $this->bind_arr,
        ];
    }
}