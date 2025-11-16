<?php

declare(strict_types=1);
class GroupBy extends MainQuery
{
    private array|string|null  $columns;
    private array $tables;

    public function __construct(EntityManagerInterface $em, array $tables, array|string|null ...$columns)
    {
        $this->columns = $columns;
        $this->em = $em;
        $this->tables = $tables;
    }

    public function getSql(): array
    {
        $columns = $this->columns;
        if (ArrayUtils::isMultidimentional($columns)) {
            $columns = ArrayUtils::flattenArrayRecursive($this->columns);
        }
        $newColumns = '';
        $tblh = $this->em->getTableAliasHelper()->setTables($this->tables);
        foreach ($columns as $key => $column) {
            $end = $key !== array_key_last($columns) ? ', ' : '';
            list($table, $column) = $tblh->mapTableColumn($column);
            list($table, $alias) = $tblh->get($table, $this->tableAlias, $this->aliasCheck);
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