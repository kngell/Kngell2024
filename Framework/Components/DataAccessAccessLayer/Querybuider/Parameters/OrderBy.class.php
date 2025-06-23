<?php

declare(strict_types=1);
class OrderBy extends MainQuery
{
    private array|string $orderBy;
    private array $tables;

    public function __construct(EntityManagerInterface $em, array $tables, array|string|null ...$orderBy)
    {
        $this->orderBy = $orderBy;
        $this->em = $em;
        $this->tables = $tables;
    }

    public function getSql(): array
    {
        $orderBy = $this->getOrderByComlumns();
        $newColumns = '';
        $tblh = $this->em->getTableAliasHelper()->setTables($this->tables);
        foreach ($orderBy as $key => $column) {
            $end = $key !== array_key_last($orderBy) ? ', ' : '';
            list($column, $sort) = $this->ascDescColumnparser($column);
            list($table, $column) = $tblh->mapTableColumn($column);
            list($table, $alias) = $tblh->get($table, $this->tableAlias, $this->aliasCheck);
            $alias = ! empty($alias) ? $alias . '.' : '';
            $newColumns .= $alias . $column . ' ' . $sort . $end;
        }
        return [$newColumns, $this->tableAlias, $this->aliasCheck, $this->parameters, $this->bind_arr,
        ];
    }

    private function getOrderByComlumns() : array
    {
        $orderBy = ArrayUtils::first($this->orderBy);
        $orderBy = $this->normalizeColumn($orderBy);
        if (ArrayUtils::isMultidimentional($orderBy)) {
            $orderBy = ArrayUtils::first($orderBy);
            if (ArrayUtils::isAssoc($orderBy)) {
                $orderBy = ArrayUtils::FromAssocToSequential($orderBy);
                $columns = [];
                foreach ($orderBy as $col => $sort) {
                    if (is_int($col)) {
                        $columns[] = $sort;
                    } else {
                        $columns[] = $col . ' ' . $sort;
                    }
                }
                return $columns;
            } else {
                $orderBy = ArrayUtils::flattenArrayRecursive($orderBy);
            }
        }
        return $orderBy;
    }

    private function ascDescColumnparser(string $column) : array
    {
        $columns = explode(' ', $column);
        if (count($columns) === 1) {
            return [$column, ''];
        }
        if (count($columns) === 2 && in_array(strtolower($columns[1]), ['asc', 'desc'])) {
            return [$columns[0], strtoupper($columns[1])];
        }
        return [];
    }

    private function normalizeColumn(array $columns) : array
    {
        $newColumns = [];
        if (isset($columns[1]) && in_array(strtolower($columns[1]), ['asc', 'desc'])) {
            $newColumns[] = $columns[0] . ' ' . $columns[1];
            unset($columns[0]);
            unset($columns[1]);
            $columns = array_values($columns);
            if (! empty($columns)) {
                $newColumns = array_merge($newColumns, $this->normalizeColumn($columns));
            }
            return $newColumns;
        }
        return $columns;
    }
}