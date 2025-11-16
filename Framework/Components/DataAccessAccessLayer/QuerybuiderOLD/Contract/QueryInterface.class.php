<?php

declare(strict_types=1);

/**
 * Provide function and method to make SQL Query
 * SELECT *
 * FROM table
 * WHERE condition
 * GROUP BY expression
 * HAVING condition
 * { UNION | INTERSECT | EXCEPT }
 * ORDER BY expression
 * LIMIT count
 * OFFSET start.
 */
interface QueryInterface
{
    public function getQuery() : string;
}