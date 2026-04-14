<?php

declare(strict_types=1);

enum SqlFunction: string
{
    public static function isSqlFunction(mixed $value): bool
    {
        if (!is_string($value)) {
            return false;
        }

        $parts = explode('(', $value);
        $funcName = strtoupper(trim($parts[0]));
        $match = self::tryFrom($funcName);

        if (!$match) {
            return false;
        }
        return str_contains($value, '(') && str_ends_with(trim($value), ')');
    }
    case COUNT = 'COUNT';
    case SUM = 'SUM';
    case AVG = 'AVG';
    case MIN = 'MIN';
    case MAX = 'MAX';

    // String functions
    case UPPER = 'UPPER';
    case LOWER = 'LOWER';
    case CONCAT = 'CONCAT';
    case SUBSTRING = 'SUBSTRING';
    case TRIM = 'TRIM';
    case LENGTH = 'LENGTH';

    // Numeric functions
    case ABS = 'ABS';
    case ROUND = 'ROUND';
    case CEIL = 'CEIL';
    case FLOOR = 'FLOOR';
    case MOD = 'MOD';

    // Date/Time functions
    case NOW = 'NOW';
    case CURDATE = 'CURDATE';
    case CURTIME = 'CURTIME';
    case DATE = 'DATE';
    case TIME = 'TIME';
    case YEAR = 'YEAR';
    case MONTH = 'MONTH';
    case DAY = 'DAY';

    // Conditional functions
    case COALESCE = 'COALESCE';
    case NULLIF = 'NULLIF';
    case CASE = 'CASE';
    case IF = 'IF';

    // Conversion functions
    case CAST = 'CAST';
    case CONVERT = 'CONVERT';
}