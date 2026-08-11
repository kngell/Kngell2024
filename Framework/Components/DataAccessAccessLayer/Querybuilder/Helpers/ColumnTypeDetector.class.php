<?php

declare(strict_types=1);

class ColumnTypeDetector
{
    public static function isComplexExpression(string $column): bool
    {
        $lowerColumn = strtolower($column);
        $trimmedColumn = trim($column);

        if ($trimmedColumn === '*') {
            return false;
        }

        if (str_contains($lowerColumn, ' as ')) {
            return true;
        }

        if (preg_match('/[+\-\/()<>!=]|\-(?!=)|\*(?!\*)/', $trimmedColumn)) {
            return true;
        }

        if (str_contains($lowerColumn, 'select ') && str_contains($lowerColumn, ' from ')) {
            return true;
        }

        if (preg_match('/[a-z_]+\([^)]*\)/i', $trimmedColumn)) {
            return true;
        }

        return false;
    }

    public static function isFunctionCall(string $column): bool
    {
        $parts = preg_split('/\s+as\s+/i', trim($column));
        $source = trim($parts[0]);
        return preg_match('/^[a-z_]+\(.*\)$/i', $source) === 1;
    }

    public static function isCountFunction(string $column): bool
    {
        return str_starts_with(strtolower(trim($column)), 'count(');
    }

    public static function isSimpleColumn(string $value): bool
    {
        return $value === '*' || preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $value) === 1;
    }
}