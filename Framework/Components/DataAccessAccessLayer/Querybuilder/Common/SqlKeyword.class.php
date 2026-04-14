<?php

declare(strict_types=1);
enum SqlKeyword: string
{
    // =============================================
    // JOIN KEYWORDS (Used in FROM clause)
    // =============================================
    case JOIN = 'JOIN';                    // <join type>
    case INNER = 'INNER';                   // <join type>
    case LEFT = 'LEFT';                     // <join type>
    case RIGHT = 'RIGHT';                   // <join type>
    case FULL = 'FULL';                     // <join type>
    case OUTER = 'OUTER';                   // <join type>
    case CROSS = 'CROSS';                   // <join type>
    case NATURAL = 'NATURAL';               // <join type>
    case ON = 'ON';                          // <join condition>
    case USING = 'USING';                    // <join column list>

    // =============================================
    // SET QUANTIFIERS
    // =============================================
    case ALL = 'ALL';                        // <set quantifier>
    case DISTINCT = 'DISTINCT';              // <set quantifier>
    case UNIQUE = 'UNIQUE';                   // <set quantifier>

    // =============================================
    // ORDER BY KEYWORDS
    // =============================================
    case ASC = 'ASC';                        // <ordering specification>
    case DESC = 'DESC';                      // <ordering specification>
    case NULLS_FIRST = 'NULLS FIRST';        // <null ordering>
    case NULLS_LAST = 'NULLS LAST';          // <null ordering>

    // =============================================
    // CASE EXPRESSION KEYWORDS
    // =============================================
    case CASE = 'CASE';
    case WHEN = 'WHEN';
    case THEN = 'THEN';
    case ELSE = 'ELSE';
    case END = 'END';

    // =============================================
    // CAST KEYWORDS
    // =============================================
    case CAST = 'CAST';
    case CONVERT = 'CONVERT';
    case COLLATE = 'COLLATE';

    // =============================================
    // PREDICATE KEYWORDS
    // =============================================
    case EXISTS = 'EXISTS';
    case UNIQUE_PRED = 'UNIQUE';              // <unique predicate>
    case MATCH = 'MATCH';                      // <match predicate>
    case OVERLAPS = 'OVERLAPS';                // <overlaps predicate>
    case SIMILAR = 'SIMILAR';                  // <similar predicate>

    // =============================================
    // QUANTIFIERS
    // =============================================
    case SOME = 'SOME';
    case ANY = 'ANY';

    // =============================================
    // VALUE KEYWORDS
    // =============================================
    case NULL = 'NULL';
    case TRUE = 'TRUE';
    case FALSE = 'FALSE';
    case UNKNOWN = 'UNKNOWN';
    case DEFAULT = 'DEFAULT';

    // =============================================
    // CTE KEYWORDS
    // =============================================
    case RECURSIVE = 'RECURSIVE';
    case MATERIALIZED = 'MATERIALIZED';        // PostgreSQL
    case NOT_MATERIALIZED = 'NOT MATERIALIZED'; // PostgreSQL

    // =============================================
    // WINDOW FRAME KEYWORDS
    // =============================================
    case UNBOUNDED = 'UNBOUNDED';
    case PRECEDING = 'PRECEDING';
    case FOLLOWING = 'FOLLOWING';
    case CURRENT_ROW = 'CURRENT ROW';
    case EXCLUDE = 'EXCLUDE';                  // SQL:2011
    case TIES = 'TIES';                         // SQL:2011
    case NO_OTHERS = 'NO OTHERS';               // SQL:2011

    // =============================================
    // TABLE KEYWORDS
    // =============================================
    case LATERAL = 'LATERAL';
    case TABLESAMPLE = 'TABLESAMPLE';
    case REPEATABLE = 'REPEATABLE';
    case SYSTEM = 'SYSTEM';                     // TABLESAMPLE method
    case BERNOULLI = 'BERNOULLI';               // TABLESAMPLE method
    case PERCENT = 'PERCENT';                    // TABLESAMPLE parameter

    // =============================================
    // LOCKING KEYWORDS
    // =============================================
    case NOWAIT = 'NOWAIT';
    case SKIP_LOCKED = 'SKIP_LOCKED';
    case WAIT = 'WAIT';                          // Some databases

    // =============================================
    // FULL-TEXT SEARCH KEYWORDS
    // =============================================
    case AGAINST = 'AGAINST';
    case IN_BOOLEAN_MODE = 'IN BOOLEAN MODE';
    case IN_NATURAL_LANGUAGE = 'IN NATURAL LANGUAGE';
    case WITH_EXPANSION = 'WITH EXPANSION';
}