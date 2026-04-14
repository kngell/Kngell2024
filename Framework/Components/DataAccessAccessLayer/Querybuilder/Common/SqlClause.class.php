<?php

declare(strict_types=1);

enum SqlClause: string
{
    public function toStatementType(): SqlStatement
    {
        return match ($this) {
            // SELECT statement clauses
            self::SELECT, self::FROM, self::WHERE,
            self::GROUP_BY, self::HAVING, self::ORDER_BY,
            self::LIMIT, self::OFFSET,self::CYCLE => SqlStatement::SELECT,

            // INSERT statement clauses
            self::INTO, self::VALUES, self::ON_DUPLICATE_KEY_UPDATE => SqlStatement::INSERT,

            // UPDATE statement clause
            self::SET => SqlStatement::UPDATE,
        };
    }
    // Data Retrieval
    case SELECT = 'SELECT';    // Projection clause
    case FROM = 'FROM';        // Data source clause
    case WHERE = 'WHERE';      // Row filter clause
    case GROUP_BY = 'GROUP BY'; // Aggregation clause
    case HAVING = 'HAVING';    // Post-aggregation filter clause
    case ORDER_BY = 'ORDER BY'; // Sorting clause
    case LIMIT = 'LIMIT';      // Result limiting clause
    case OFFSET = 'OFFSET';    // Result pagination clause

    // Data Modification
    case SET = 'SET';          // Column assignment clause (UPDATE)
    case VALUES = 'VALUES';    // Row insertion clause (INSERT)
    case INTO = 'INTO';        // Target specification clause (INSERT)
    case ON_DUPLICATE_KEY_UPDATE = 'ON DUPLICATE KEY UPDATE';

    // Recursive CTE support
    case SEARCH = 'SEARCH';
    case CYCLE = 'CYCLE';
}