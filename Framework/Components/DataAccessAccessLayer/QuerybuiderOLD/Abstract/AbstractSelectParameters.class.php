<?php

declare(strict_types=1);
abstract class AbstractSelectParameters extends MainQuery
{
    public function getColumns(array $columns, string $table, string $statement) : string
    {
        if (ArrayUtils::isMultidimentional($columns)) {
            $columns = ArrayUtils::flattenArrayRecursive($columns);
        }
        $newColumns = '';
        foreach ($columns as $key => $column) {
            $end = $key !== array_key_last($columns) ? ', ' : '';
            $newColumns .= $table . '.' . $column . $end;
        }
        return $statement . ' ' . $newColumns;
    }
}