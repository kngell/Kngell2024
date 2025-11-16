<?php

declare(strict_types=1);

/**
 * SQL FUNCTIONS - Built-in operations that return a value
 * These are called within SQL expressions, not as standalone methods.
 */
enum SqlFunction: string
{
    // =========================================================================
    // AGGREGATE FUNCTIONS - Operate on multiple rows, return single value
    // =========================================================================
    case COUNT = 'COUNT';
    case SUM = 'SUM';
    case AVG = 'AVG';
    case MIN = 'MIN';
    case MAX = 'MAX';

    // =========================================================================
    // SCALAR FUNCTIONS - Operate on single value, return single value
    // =========================================================================
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