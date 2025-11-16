<?php

declare(strict_types=1);

class ColumnTypeDetector
{
    public static function isComplexExpression(string $column): bool
    {
        $lowerColumn = strtolower($column);
        $trimmedColumn = trim($column);

        // Allow standalone * (wildcard for all columns)
        if ($trimmedColumn === '*') {
            return false;
        }

        // Already has AS alias
        if (str_contains($lowerColumn, ' as ')) {
            return true;
        }

        // Complex expression with operators (but exclude standalone *)
        // Improved pattern to detect actual operators, not just wildcard *
        if (preg_match('/[+\-\/()<>!=]|\-(?!=)|\*(?!\*)/', $trimmedColumn)) {
            return true;
        }

        // Subquery
        if (str_contains($lowerColumn, 'select ') && str_contains($lowerColumn, ' from ')) {
            return true;
        }

        // Database function
        if (preg_match('/[a-z_]+\([^)]*\)/i', $trimmedColumn)) {
            return true;
        }

        return false;
    }

    public static function isFunctionCall(string $column): bool
    {
        return preg_match('/^[a-z_]+\(.*\)$/i', $column) === 1;
    }

    public static function isCountFunction(string $column): bool
    {
        return str_starts_with(strtolower(trim($column)), 'count(');
    }

    public static function isSimpleColumn(string $value): bool
    {
        return preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $value) === 1;
    }
}