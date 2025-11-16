<?php

declare(strict_types=1);
/**
 * SQL FUNCTION CATEGORIES - Strict classification.
 */
enum SqlFunctionCategory: string
{
    case AGGREGATE = 'aggregate';
    case SCALAR = 'scalar';
    case WINDOW = 'window';
}