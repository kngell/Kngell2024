<?php

declare(strict_types=1);

/**
 * SQL OPERATORS - Complete set of SQL expression components
 * Covers all standard SQL operators across all categories.
 */
enum SqlOperator: string
{
    // =============================================
    // OPERATOR CATEGORY METHODS
    // =============================================

    public function isBinary(): bool
    {
        return in_array($this, [
            // Comparison
            self::EQUALS, self::NOT_EQUALS, self::NOT_EQUALS_ALT,
            self::GREATER_THAN, self::LESS_THAN,
            self::GREATER_THAN_OR_EQUAL, self::LESS_THAN_OR_EQUAL,

            // Pattern matching
            self::LIKE, self::NOT_LIKE, self::ILIKE,
            self::REGEXP, self::NOT_REGEXP,
            self::SIMILAR_TO, self::NOT_SIMILAR_TO,

            // Set
            self::IN, self::NOT_IN, self::BETWEEN, self::NOT_BETWEEN,

            // Arithmetic
            self::ADD, self::SUBTRACT, self::MULTIPLY, self::DIVIDE,
            self::MODULO, self::EXPONENT,

            // Bitwise
            self::BITWISE_AND, self::BITWISE_OR, self::BITWISE_XOR,
            self::BITWISE_LEFT_SHIFT, self::BITWISE_RIGHT_SHIFT,

            // String
            self::CONCAT,

            // JSON
            self::JSON_EXTRACT, self::JSON_EXTRACT_TEXT,
            self::JSON_PATH_EXTRACT, self::JSON_PATH_EXTRACT_TEXT,
            self::JSON_CONTAINS, self::JSON_IS_CONTAINED,
            self::JSON_HAS_KEY, self::JSON_HAS_ANY_KEYS, self::JSON_HAS_ALL_KEYS,

            // Array
            self::ARRAY_CONTAINS, self::ARRAY_IS_CONTAINED,
            self::ARRAY_OVERLAP, self::ARRAY_CONCAT,

            // Spatial
            self::SPATIAL_EQUALS, self::SPATIAL_INTERSECTS,
            self::SPATIAL_CONTAINS, self::SPATIAL_WITHIN,
            self::SPATIAL_OVERLAPS, self::SPATIAL_TOUCHES,
            self::SPATIAL_CROSSES, self::SPATIAL_DWITHIN,
        ]);
    }

    public function isUnary(): bool
    {
        return in_array($this, [
            // Null checks
            self::IS_NULL, self::IS_NOT_NULL,
            self::IS_TRUE, self::IS_FALSE, self::IS_UNKNOWN,
            self::IS_NOT_TRUE, self::IS_NOT_FALSE, self::IS_NOT_UNKNOWN,

            // Existence checks
            self::EXISTS, self::NOT_EXISTS,

            // Bitwise
            self::BITWISE_NOT,

            // Arithmetic
            self::FACTORIAL,

            // Set operations (prefix)
            self::ALL, self::ANY, self::SOME,
            self::DISTINCT,
        ]);
    }

    public function isLogical(): bool
    {
        return in_array($this, [
            self::AND, self::OR, self::NOT, self::XOR,
        ]);
    }

    public function isJoinRelated(): bool
    {
        return in_array($this, [
            self::ON, self::USING, self::NATURAL,
        ]);
    }

    public function isSetOperation(): bool
    {
        return in_array($this, [
            self::UNION, self::UNION_ALL,
            self::INTERSECT, self::INTERSECT_ALL,
            self::EXCEPT, self::EXCEPT_ALL,
            self::MINUS,
        ]);
    }

    public function getPrecedence(): int
    {
        return match($this) {
            // Highest precedence
            self::BITWISE_NOT, self::FACTORIAL => 100,
            self::MULTIPLY, self::DIVIDE, self::MODULO => 90,
            self::ADD, self::SUBTRACT => 80,
            self::BITWISE_LEFT_SHIFT, self::BITWISE_RIGHT_SHIFT => 70,
            self::BITWISE_AND => 60,
            self::BITWISE_XOR => 50,
            self::BITWISE_OR => 40,
            // Comparison operators
            self::EQUALS, self::NOT_EQUALS, self::NOT_EQUALS_ALT,
            self::GREATER_THAN, self::LESS_THAN,
            self::GREATER_THAN_OR_EQUAL, self::LESS_THAN_OR_EQUAL => 30,
            self::IS_NULL, self::IS_NOT_NULL,
            self::IS_TRUE, self::IS_FALSE, self::IS_UNKNOWN,
            self::IS_NOT_TRUE, self::IS_NOT_FALSE, self::IS_NOT_UNKNOWN => 25,
            self::LIKE, self::NOT_LIKE, self::ILIKE,
            self::REGEXP, self::NOT_REGEXP,
            self::SIMILAR_TO, self::NOT_SIMILAR_TO => 20,
            self::BETWEEN, self::NOT_BETWEEN => 15,
            self::IN, self::NOT_IN => 10,
            self::AND => 5,
            self::OR, self::XOR => 1,
            // Lowest precedence
            default => 0
        };
    }

    // =====================================
    // Helper: return SQL-safe pure symbol
    // =====================================
    public function toSql(): string
    {
        // Remove suffix like "_JSON", "_ARR", etc.
        return preg_replace('/_[A-Z0-9]+$/', '', $this->value);
    }
    // LOGICAL
    case AND = 'AND';
    case OR = 'OR';
    case NOT = 'NOT';
    case XOR = 'XOR';

    // COMPARISON
    case EQUALS = '=';
    case NOT_EQUALS = '<>';
    case NOT_EQUALS_ALT = '!=';
    case GREATER_THAN = '>';
    case LESS_THAN = '<';
    case GREATER_THAN_OR_EQUAL = '>=';
    case LESS_THAN_OR_EQUAL = '<=';

    // PATTERN MATCHING
    case LIKE = 'LIKE';
    case NOT_LIKE = 'NOT LIKE';
    case ILIKE = 'ILIKE';
    case REGEXP = 'REGEXP';
    case NOT_REGEXP = 'NOT REGEXP';
    case SIMILAR_TO = 'SIMILAR TO';
    case NOT_SIMILAR_TO = 'NOT SIMILAR TO';

    // SET
    case IN = 'IN';
    case NOT_IN = 'NOT IN';
    case BETWEEN = 'BETWEEN';
    case NOT_BETWEEN = 'NOT BETWEEN';
    case ANY = 'ANY';
    case SOME = 'SOME';
    case ALL = 'ALL';
    case EXISTS = 'EXISTS';
    case NOT_EXISTS = 'NOT EXISTS';

    // NULL / BOOLEAN
    case IS_NULL = 'IS NULL';
    case IS_NOT_NULL = 'IS NOT NULL';
    case IS_TRUE = 'IS TRUE';
    case IS_FALSE = 'IS FALSE';
    case IS_UNKNOWN = 'IS UNKNOWN';
    case IS_NOT_TRUE = 'IS NOT TRUE';
    case IS_NOT_FALSE = 'IS NOT FALSE';
    case IS_NOT_UNKNOWN = 'IS NOT UNKNOWN';

    // BITWISE
    case BITWISE_AND = '&';
    case BITWISE_OR = '|';
    case BITWISE_XOR = '^';
    case BITWISE_NOT = '~';
    case BITWISE_LEFT_SHIFT = '<<';
    case BITWISE_RIGHT_SHIFT = '>>';

    // ARITHMETIC
    case ADD = '+';
    case SUBTRACT = '-';
    case MULTIPLY = '*';
    case DIVIDE = '/';
    case MODULO = '%';
    case EXPONENT = '**'; // PostgreSQL style
    case FACTORIAL = '!';

    // STRING
    case CONCAT = '||';
    case CONCAT_WS = '||_WS';
    case CONCAT_WITH_SPACE = '||_SPC';

    // JSON
    case JSON_EXTRACT = '->';
    case JSON_EXTRACT_TEXT = '->>';
    case JSON_PATH_EXTRACT = '#>';
    case JSON_PATH_EXTRACT_TEXT = '#>>';
    case JSON_CONTAINS = '@>_JSON';
    case JSON_IS_CONTAINED = '<@_JSON';
    case JSON_HAS_KEY = '?_JSON';
    case JSON_HAS_ANY_KEYS = '?|_JSON';
    case JSON_HAS_ALL_KEYS = '?&_JSON';

    // ARRAY
    case ARRAY_CONTAINS = '@>_ARR';
    case ARRAY_IS_CONTAINED = '<@_ARR';
    case ARRAY_OVERLAP = '&&_ARR';
    case ARRAY_CONCAT = '||_ARR';

    // SPATIAL
    case SPATIAL_EQUALS = '=_SPAT';
    case SPATIAL_INTERSECTS = '&&_SPAT';
    case SPATIAL_CONTAINS = '@>_SPAT';
    case SPATIAL_WITHIN = '<@_SPAT';
    case SPATIAL_OVERLAPS = '&&_SPAT2';
    case SPATIAL_TOUCHES = '~=_SPAT';
    case SPATIAL_CROSSES = '#_SPAT';
    case SPATIAL_DWITHIN = '<->_SPAT';

    // JOIN
    case ON = 'ON';
    case USING = 'USING';
    case NATURAL = 'NATURAL';

    // SET OPERATIONS
    case UNION = 'UNION';
    case UNION_ALL = 'UNION ALL';
    case INTERSECT = 'INTERSECT';
    case INTERSECT_ALL = 'INTERSECT ALL';
    case EXCEPT = 'EXCEPT';
    case EXCEPT_ALL = 'EXCEPT ALL';
    case MINUS = 'MINUS';

    // WINDOW
    case OVER = 'OVER';
    case PARTITION_BY = 'PARTITION BY';
    case ORDER_BY_OP = 'ORDER BY';
    case ROWS = 'ROWS';
    case RANGE = 'RANGE';
    case PRECEDING = 'PRECEDING';
    case FOLLOWING = 'FOLLOWING';
    case CURRENT_ROW = 'CURRENT ROW';
    case UNBOUNDED = 'UNBOUNDED';

    // CASE
    case CASE = 'CASE';
    case WHEN = 'WHEN';
    case THEN = 'THEN';
    case ELSE = 'ELSE';
    case END = 'END';

    // CAST
    case CAST = 'CAST';
    case CAST_OPERATOR = '::';
    case CONVERT = 'CONVERT';

    // COLLATION
    case COLLATE = 'COLLATE';

    // MISC
    case DISTINCT = 'DISTINCT';
    case FILTER = 'FILTER';
    case WITHIN_GROUP = 'WITHIN GROUP';
    case LATERAL = 'LATERAL';
    case TABLESAMPLE = 'TABLESAMPLE';
    case MATCH = 'MATCH';
    case AGAINST = 'AGAINST';
}