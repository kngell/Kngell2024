<?php

declare(strict_types=1);

class ColumnStandardizer
{
    public static function standardize(array $columns): array
    {
        $standardized = $columns;

        if (ArrayUtils::isMultidimentional($standardized)) {
            $standardized = ArrayUtils::flattenArrayRecursive($columns);
            if (empty($standardized)) {
                $standardized = ['*'];
            }
        }

        if (ArrayUtils::isMultidimentional($standardized)) {
            return self::standardize($standardized);
        }

        return $standardized;
    }

    public static function isValidColumnName(string $column): bool
    {
        return preg_match('/^[a-zA-Z0-9_*]+$/', $column) === 1;
    }

    public static function isQualifiedColumn(string $column): bool
    {
        $separator = strpos($column, '|') !== false ? '|' : '.';
        $parts = explode($separator, $column);
        return count($parts) === 2 && !empty($parts[0]) && !empty($parts[1]);
    }
}