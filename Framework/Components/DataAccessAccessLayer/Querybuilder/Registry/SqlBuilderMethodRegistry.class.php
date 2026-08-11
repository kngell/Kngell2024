<?php

declare(strict_types=1);

/**
 * COMPREHENSIVE BUILDER METHOD REGISTRY
 * Single source of truth for method → SQL concept mappings.
 */
final class SqlBuilderMethodRegistry
{
    private const METHOD_MAPPINGS = [
        // ====================================
        // WHERE CLAUSE METHODS
        // ====================================
        'where' => [
            'clause' => SqlClause::WHERE,
            'link' => SqlConditionLink::AND,
            'operator' => SqlOperator::EQUALS,
        ],
        'andWhere' => [
            'clause' => SqlClause::WHERE,
            'link' => SqlConditionLink::AND,
            'operator' => SqlOperator::EQUALS,
        ],
        'orWhere' => [
            'clause' => SqlClause::WHERE,
            'link' => SqlConditionLink::OR,
            'operator' => SqlOperator::EQUALS,
        ],

        // Comparison methods
        'whereColumn' => [
            'clause' => SqlClause::WHERE,
            'link' => SqlConditionLink::AND,
            'operator' => SqlOperator::EQUALS,
        ],
        'whereEqualTo' => [
            'clause' => SqlClause::WHERE,
            'link' => SqlConditionLink::AND,
            'operator' => SqlOperator::EQUALS,
        ],
        'whereNotEqualTo' => [
            'clause' => SqlClause::WHERE,
            'link' => SqlConditionLink::AND,
            'operator' => SqlOperator::NOT_EQUALS,
        ],
        'whereLessThan' => [
            'clause' => SqlClause::WHERE,
            'link' => SqlConditionLink::AND,
            'operator' => SqlOperator::LESS_THAN,
        ],
        'whereGreaterThan' => [
            'clause' => SqlClause::WHERE,
            'link' => SqlConditionLink::AND,
            'operator' => SqlOperator::GREATER_THAN,
        ],
        'whereLessOrEqualTo' => [
            'clause' => SqlClause::WHERE,
            'link' => SqlConditionLink::AND,
            'operator' => SqlOperator::LESS_THAN_OR_EQUAL,
        ],
        'whereGreaterOrEqualTo' => [
            'clause' => SqlClause::WHERE,
            'link' => SqlConditionLink::AND,
            'operator' => SqlOperator::GREATER_THAN_OR_EQUAL,
        ],

        // OR variants
        'orWhereEqualTo' => [
            'clause' => SqlClause::WHERE,
            'link' => SqlConditionLink::OR,
            'operator' => SqlOperator::EQUALS,
        ],
        'orWhereNotEqualTo' => [
            'clause' => SqlClause::WHERE,
            'link' => SqlConditionLink::OR,
            'operator' => SqlOperator::NOT_EQUALS,
        ],
        'orWhereLessThan' => [
            'clause' => SqlClause::WHERE,
            'link' => SqlConditionLink::OR,
            'operator' => SqlOperator::LESS_THAN,
        ],
        'orWhereGreaterThan' => [
            'clause' => SqlClause::WHERE,
            'link' => SqlConditionLink::OR,
            'operator' => SqlOperator::GREATER_THAN,
        ],

        'xorWhere' => [
            'clause' => SqlClause::WHERE,
            'link' => SqlConditionLink::XOR,  // ← Add this
            'operator' => SqlOperator::EQUALS,
        ],
        'xorHaving' => [
            'clause' => SqlClause::HAVING,
            'link' => SqlConditionLink::XOR,  // ← Add this
            'operator' => SqlOperator::EQUALS,
        ],
        // =========================================
        // PATTERN MATCHING METHODS
        // =========================================
        'whereLike' => [
            'clause' => SqlClause::WHERE,
            'link' => SqlConditionLink::AND,
            'operator' => SqlOperator::LIKE,
        ],
        'whereNotLike' => [
            'clause' => SqlClause::WHERE,
            'link' => SqlConditionLink::AND,
            'operator' => SqlOperator::NOT_LIKE,
        ],
        'whereILike' => [
            'clause' => SqlClause::WHERE,
            'link' => SqlConditionLink::AND,
            'operator' => SqlOperator::ILIKE,
        ],
        'whereRegexp' => [
            'clause' => SqlClause::WHERE,
            'link' => SqlConditionLink::AND,
            'operator' => SqlOperator::REGEXP,
        ],
        'whereNotRegexp' => [
            'clause' => SqlClause::WHERE,
            'link' => SqlConditionLink::AND,
            'operator' => SqlOperator::NOT_REGEXP,
        ],

        'orWhereLike' => [
            'clause' => SqlClause::WHERE,
            'link' => SqlConditionLink::OR,
            'operator' => SqlOperator::LIKE,
        ],
        'orWhereNotLike' => [
            'clause' => SqlClause::WHERE,
            'link' => SqlConditionLink::OR,
            'operator' => SqlOperator::NOT_LIKE,
        ],

        // =============================================
        // SET OPERATIONS METHODS
        // =============================================
        'whereIn' => [
            'clause' => SqlClause::WHERE,
            'link' => SqlConditionLink::AND,
            'operator' => SqlOperator::IN,
        ],
        'whereNotIn' => [
            'clause' => SqlClause::WHERE,
            'link' => SqlConditionLink::AND,
            'operator' => SqlOperator::NOT_IN,
        ],
        'whereBetween' => [
            'clause' => SqlClause::WHERE,
            'link' => SqlConditionLink::AND,
            'operator' => SqlOperator::BETWEEN,
        ],
        'whereNotBetween' => [
            'clause' => SqlClause::WHERE,
            'link' => SqlConditionLink::AND,
            'operator' => SqlOperator::NOT_BETWEEN,
        ],
        'whereExists' => [
            'clause' => SqlClause::WHERE,
            'link' => SqlConditionLink::AND,
            'operator' => SqlOperator::EXISTS,
        ],
        'whereNotExists' => [
            'clause' => SqlClause::WHERE,
            'link' => SqlConditionLink::AND,
            'operator' => SqlOperator::NOT_EXISTS,
        ],
        'whereAny' => [
            'clause' => SqlClause::WHERE,
            'link' => SqlConditionLink::AND,
            'operator' => SqlOperator::ANY,
        ],
        'whereSome' => [
            'clause' => SqlClause::WHERE,
            'link' => SqlConditionLink::AND,
            'operator' => SqlOperator::SOME,
        ],
        'whereAll' => [
            'clause' => SqlClause::WHERE,
            'link' => SqlConditionLink::AND,
            'operator' => SqlOperator::ALL,
        ],

        'orWhereIn' => [
            'clause' => SqlClause::WHERE,
            'link' => SqlConditionLink::OR,
            'operator' => SqlOperator::IN,
        ],
        'orWhereNotIn' => [
            'clause' => SqlClause::WHERE,
            'link' => SqlConditionLink::OR,
            'operator' => SqlOperator::NOT_IN,
        ],
        'orWhereBetween' => [
            'clause' => SqlClause::WHERE,
            'link' => SqlConditionLink::OR,
            'operator' => SqlOperator::BETWEEN,
        ],
        'unionAll' => [
            'clause' => SqlClause::SELECT,
            'link' => null,
            'operator' => SqlOperator::UNION_ALL,
        ],

        // ==========================================
        // NULL OPERATIONS METHODS
        // ==========================================
        'when' => [
            'clause' => null,
            'link' => null,
            'operator' => SqlOperator::EQUALS,
        ],
        'whereNull' => [
            'clause' => SqlClause::WHERE,
            'link' => SqlConditionLink::AND,
            'operator' => SqlOperator::IS_NULL,
        ],
        'whereNotNull' => [
            'clause' => SqlClause::WHERE,
            'link' => SqlConditionLink::AND,
            'operator' => SqlOperator::IS_NOT_NULL,
        ],
        'whereTrue' => [
            'clause' => SqlClause::WHERE,
            'link' => SqlConditionLink::AND,
            'operator' => SqlOperator::IS_TRUE,
        ],
        'whereFalse' => [
            'clause' => SqlClause::WHERE,
            'link' => SqlConditionLink::AND,
            'operator' => SqlOperator::IS_FALSE,
        ],

        'orWhereNull' => [
            'clause' => SqlClause::WHERE,
            'link' => SqlConditionLink::OR,
            'operator' => SqlOperator::IS_NULL,
        ],
        'orWhereNotNull' => [
            'clause' => SqlClause::WHERE,
            'link' => SqlConditionLink::OR,
            'operator' => SqlOperator::IS_NOT_NULL,
        ],

        // ========================================
        // FROM/JOIN METHODS
        // ========================================
        'from' => [
            'clause' => SqlClause::FROM,
            'link' => null,
            'operator' => SqlOperator::EQUALS,
        ],
        'join' => [
            'clause' => SqlClause::FROM,
            'link' => null,
            'operator' => SqlOperator::EQUALS,
        ],
        // JOIN keywords (link is null - these are not conditions)
        'innerJoin' => [
            'clause' => SqlClause::FROM,
            'link' => null,
            'operator' => SqlOperator::EQUALS,
        ],
        'leftJoin' => [
            'clause' => SqlClause::FROM,
            'link' => null,
            'operator' => SqlOperator::EQUALS,
        ],
        'rightJoin' => [
            'clause' => SqlClause::FROM,
            'link' => null,
            'operator' => SqlOperator::EQUALS,
        ],
        'crossJoin' => [
            'clause' => SqlClause::FROM,
            'link' => null,
            'operator' => SqlOperator::EQUALS,
        ],
        'fullJoin' => [
            'clause' => SqlClause::FROM,
            'link' => null,
            'operator' => SqlOperator::EQUALS,
        ],
        'naturalJoin' => [
            'clause' => SqlClause::FROM,
            'link' => null,
            'operator' => SqlOperator::EQUALS,
        ],

        // Short form JOIN keywords
        'inner' => [
            'clause' => SqlClause::FROM,
            'link' => null,
            'operator' => SqlOperator::EQUALS,
        ],
        'left' => [
            'clause' => SqlClause::FROM,
            'link' => null,
            'operator' => SqlOperator::EQUALS,
        ],
        'right' => [
            'clause' => SqlClause::FROM,
            'link' => null,
            'operator' => SqlOperator::EQUALS,
        ],
        'cross' => [
            'clause' => SqlClause::FROM,
            'link' => null,
            'operator' => SqlOperator::EQUALS,
        ],
        'full' => [
            'clause' => SqlClause::FROM,
            'link' => null,
            'operator' => SqlOperator::EQUALS,
        ],
        'natural' => [
            'clause' => SqlClause::FROM,
            'link' => null,
            'operator' => SqlOperator::EQUALS,
        ],

        // ON condition methods (link is ON)
        'on' => [
            'clause' => SqlClause::FROM,
            'link' => SqlConditionLink::ON,
            'operator' => SqlOperator::EQUALS,
        ],
        'andOn' => [
            'clause' => SqlClause::FROM,
            'link' => SqlConditionLink::ON,
            'operator' => SqlOperator::EQUALS,
        ],
        'orOn' => [
            'clause' => SqlClause::FROM,
            'link' => SqlConditionLink::ON,
            'operator' => SqlOperator::EQUALS,
        ],
        'onEqualTo' => [
            'clause' => SqlClause::FROM,
            'link' => SqlConditionLink::ON,
            'operator' => SqlOperator::EQUALS,
        ],
        'onNotEqualTo' => [
            'clause' => SqlClause::FROM,
            'link' => SqlConditionLink::ON,
            'operator' => SqlOperator::NOT_EQUALS,
        ],
        'onLessThan' => [
            'clause' => SqlClause::FROM,
            'link' => SqlConditionLink::ON,
            'operator' => SqlOperator::LESS_THAN,
        ],
        'onGreaterThan' => [
            'clause' => SqlClause::FROM,
            'link' => SqlConditionLink::ON,
            'operator' => SqlOperator::GREATER_THAN,
        ],
        'onLike' => [
            'clause' => SqlClause::FROM,
            'link' => SqlConditionLink::ON,
            'operator' => SqlOperator::LIKE,
        ],
        'onIn' => [
            'clause' => SqlClause::FROM,
            'link' => SqlConditionLink::ON,
            'operator' => SqlOperator::IN,
        ],
        'onBetween' => [
            'clause' => SqlClause::FROM,
            'link' => SqlConditionLink::ON,
            'operator' => SqlOperator::BETWEEN,
        ],
        'onNull' => [
            'clause' => SqlClause::FROM,
            'link' => SqlConditionLink::ON,
            'operator' => SqlOperator::IS_NULL,
        ],
        'onClosure' => [
            'clause' => SqlClause::FROM,
            'link' => SqlConditionLink::ON,
            'operator' => SqlOperator::EQUALS,
        ],
        'onValue' => [
            'clause' => SqlClause::FROM,
            'link' => SqlConditionLink::AND,
            'operator' => SqlOperator::EQUALS,
        ],
        'orOnValue' => [
            'clause' => SqlClause::FROM,
            'link' => SqlConditionLink::OR,
            'operator' => SqlOperator::EQUALS,
        ],

        // ========================================
        // HAVING CLAUSE METHODS
        // ========================================
        'having' => [
            'clause' => SqlClause::HAVING,
            'link' => SqlConditionLink::AND,
            'operator' => SqlOperator::EQUALS,
        ],
        'andHaving' => [
            'clause' => SqlClause::HAVING,
            'link' => SqlConditionLink::AND,
            'operator' => SqlOperator::EQUALS,
        ],
        'orHaving' => [
            'clause' => SqlClause::HAVING,
            'link' => SqlConditionLink::OR,
            'operator' => SqlOperator::EQUALS,
        ],

        'havingEqualTo' => [
            'clause' => SqlClause::HAVING,
            'link' => SqlConditionLink::AND,
            'operator' => SqlOperator::EQUALS,
        ],
        'havingGreaterThan' => [
            'clause' => SqlClause::HAVING,
            'link' => SqlConditionLink::AND,
            'operator' => SqlOperator::GREATER_THAN,
        ],
        'havingLessThan' => [
            'clause' => SqlClause::HAVING,
            'link' => SqlConditionLink::AND,
            'operator' => SqlOperator::LESS_THAN,
        ],
        'havingIn' => [
            'clause' => SqlClause::HAVING,
            'link' => SqlConditionLink::AND,
            'operator' => SqlOperator::IN,
        ],
        'havingLike' => [
            'clause' => SqlClause::HAVING,
            'link' => SqlConditionLink::AND,
            'operator' => SqlOperator::LIKE,
        ],

        // ========================================
        // EXPRESSION/OPERATION METHODS (for SELECT, SET, etc.)
        // ========================================
        'add' => [
            'clause' => SqlClause::SELECT,
            'link' => null,
            'operator' => SqlOperator::ADD,
        ],
        'subtract' => [
            'clause' => SqlClause::SELECT,
            'link' => null,
            'operator' => SqlOperator::SUBTRACT,
        ],
        'multiply' => [
            'clause' => SqlClause::SELECT,
            'link' => null,
            'operator' => SqlOperator::MULTIPLY,
        ],
        'divide' => [
            'clause' => SqlClause::SELECT,
            'link' => null,
            'operator' => SqlOperator::DIVIDE,
        ],
        'modulo' => [
            'clause' => SqlClause::SELECT,
            'link' => null,
            'operator' => SqlOperator::MODULO,
        ],

        'bitwiseAnd' => [
            'clause' => SqlClause::SELECT,
            'link' => null,
            'operator' => SqlOperator::BITWISE_AND,
        ],
        'bitwiseOr' => [
            'clause' => SqlClause::SELECT,
            'link' => null,
            'operator' => SqlOperator::BITWISE_OR,
        ],
        'bitwiseXor' => [
            'clause' => SqlClause::SELECT,
            'link' => null,
            'operator' => SqlOperator::BITWISE_XOR,
        ],
        'bitwiseNot' => [
            'clause' => SqlClause::SELECT,
            'link' => null,
            'operator' => SqlOperator::BITWISE_NOT,
        ],

        'concat' => [
            'clause' => SqlClause::SELECT,
            'link' => null,
            'operator' => SqlOperator::CONCAT,
        ],

        'jsonExtract' => [
            'clause' => SqlClause::SELECT,
            'link' => null,
            'operator' => SqlOperator::JSON_EXTRACT,
        ],
        'jsonExtractText' => [
            'clause' => SqlClause::SELECT,
            'link' => null,
            'operator' => SqlOperator::JSON_EXTRACT_TEXT,
        ],
        'jsonContains' => [
            'clause' => SqlClause::WHERE,
            'link' => SqlConditionLink::AND,
            'operator' => SqlOperator::JSON_CONTAINS,
        ],
        'jsonHasKey' => [
            'clause' => SqlClause::WHERE,
            'link' => SqlConditionLink::AND,
            'operator' => SqlOperator::JSON_HAS_KEY,
        ],

        'arrayContains' => [
            'clause' => SqlClause::WHERE,
            'link' => SqlConditionLink::AND,
            'operator' => SqlOperator::ARRAY_CONTAINS,
        ],
        'arrayOverlap' => [
            'clause' => SqlClause::WHERE,
            'link' => SqlConditionLink::AND,
            'operator' => SqlOperator::ARRAY_OVERLAP,
        ],

        // Insert Clauses
        'into' => [
            'clause' => SqlClause::INTO,
            'link' => null,
            'operator' => null,
        ],
        'values' => [
            'clause' => SqlClause::VALUES,
            'link' => null,
            'operator' => null,
        ],

        // Update Clauses
        'set' => [
            'clause' => SqlClause::SET,
            'link' => null,
            'operator' => SqlOperator::EQUALS,
        ],
    ];

    // ========================================
    // PUBLIC API - Method Classification
    // ========================================

    /**
     * Get complete mapping for a method.
     */
    public static function getMapping(string $method): array
    {
        return self::METHOD_MAPPINGS[$method] ?? [
            'clause' => SqlClause::WHERE,
            'link' => SqlConditionLink::AND,
            'operator' => SqlOperator::EQUALS,
        ];
    }

    /**
     * Get clause context for a method.
     */
    public static function getClauseContext(string $method): SqlClause
    {
        if ($method === 'when') {
            return SqlKeyword::WHEN->toSqlClause();
        }
        return self::getMapping($method)['clause'];
    }

    /**
     * Get logical link for a method.
     */
    public static function getLogicalLink(string $method): ?SqlConditionLink
    {
        return self::getMapping($method)['link'];
    }

    /**
     * Get default operator for a method.
     */
    public static function getDefaultOperator(string $method): ?SqlOperator
    {
        return self::getMapping($method)['operator'];
    }

    /**
     * Check if method exists in registry.
     */
    public static function isValidMethod(string $method): bool
    {
        return array_key_exists($method, self::METHOD_MAPPINGS);
    }

    // ========================================
    // JOIN/ON CLASSIFICATION METHODS
    // ========================================

    public static function isJoinMethod(string $method): bool
    {
        $mapping = self::getMapping($method);

        // Must be FROM clause
        if ($mapping['clause'] !== SqlClause::FROM) {
            return false;
        }

        // Link must be null (JOIN keywords are not conditions)
        if ($mapping['link'] !== null) {
            return false;
        }

        // Exclude the main 'from' method (table source)
        if ($method === 'from') {
            return false;
        }

        // Exclude ON methods (though they'd be caught by link !== null anyway)
        if (self::isOnMethod($method)) {
            return false;
        }

        return true;
    }

    public static function isOnMethod(string $method): bool
    {
        $mapping = self::getMapping($method);

        // Must be FROM clause
        if ($mapping['clause'] !== SqlClause::FROM) {
            return false;
        }

        // Link must be ON (primary indicator)
        if ($mapping['link'] === SqlConditionLink::ON) {
            return true;
        }

        // Fallback: method name starts with 'on' (convention)
        // This catches any method that follows the naming convention
        if (str_starts_with($method, 'on')) {
            return true;
        }

        return false;
    }

    /**
     * Check if a method is the main FROM clause (table source).
     */
    public static function isFromMethod(string $method): bool
    {
        return $method === 'from';
    }

    /**
     * Get all JOIN keyword methods.
     */
    public static function getJoinMethods(): array
    {
        static $cachedJoinMethods = null;

        if ($cachedJoinMethods === null) {
            $cachedJoinMethods = array_filter(
                array_keys(self::METHOD_MAPPINGS),
                fn ($method) => self::isJoinMethod($method),
            );
            $cachedJoinMethods = array_values($cachedJoinMethods);
        }

        return $cachedJoinMethods;
    }

    /**
     * Get all ON condition methods.
     */
    public static function getOnMethods(): array
    {
        static $cachedOnMethods = null;

        if ($cachedOnMethods === null) {
            $cachedOnMethods = array_filter(
                array_keys(self::METHOD_MAPPINGS),
                fn ($method) => self::isOnMethod($method),
            );
            $cachedOnMethods = array_values($cachedOnMethods);
        }

        return $cachedOnMethods;
    }

    /**
     * Check if a method is a WHERE condition method.
     */
    public static function isWhereMethod(string $method): bool
    {
        $mapping = self::getMapping($method);
        return $mapping['clause'] === SqlClause::WHERE;
    }

    /**
     * Check if a method is a HAVING condition method.
     */
    public static function isHavingMethod(string $method): bool
    {
        $mapping = self::getMapping($method);
        return $mapping['clause'] === SqlClause::HAVING;
    }

    /**
     * Check if a method uses AND logic (for WHERE/HAVING).
     */
    public static function isAndMethod(string $method): bool
    {
        $link = self::getLogicalLink($method);
        return $link === SqlConditionLink::AND;
    }

    /**
     * Check if a method uses OR logic (for WHERE/HAVING).
     */
    public static function isOrMethod(string $method): bool
    {
        $link = self::getLogicalLink($method);
        return $link === SqlConditionLink::OR;
    }

    // ========================================
    // UTILITY METHODS
    // ========================================

    /**
     * Get all methods that use a specific operator.
     */
    public static function getMethodsForOperator(SqlOperator $operator): array
    {
        return array_keys(array_filter(
            self::METHOD_MAPPINGS,
            fn ($mapping) => $mapping['operator'] === $operator,
        ));
    }

    /**
     * Get all methods for a specific clause context.
     */
    public static function getMethodsForClause(SqlClause $clause): array
    {
        return array_keys(array_filter(
            self::METHOD_MAPPINGS,
            fn ($mapping) => $mapping['clause'] === $clause,
        ));
    }

    /**
     * Get all registered methods.
     */
    public static function getAllMethods(): array
    {
        return array_keys(self::METHOD_MAPPINGS);
    }
}