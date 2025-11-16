<?php

declare(strict_types=1);
/**
 * SQL JOIN TYPES - Specifications for table relationships within FROM clause
 * These are NOT clauses but modifiers for the FROM clause.
 */
enum SqlJoinType: string
{
    case INNER = 'INNER JOIN';
    case LEFT = 'LEFT JOIN';
    case RIGHT = 'RIGHT JOIN';
    case FULL = 'FULL JOIN';
    case CROSS = 'CROSS JOIN';
}