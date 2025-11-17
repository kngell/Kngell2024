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

        // ==========================================
        // NULL OPERATIONS METHODS
        // ==========================================
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
        // JOIN CONDITION METHODS (ON clause - part of FROM)
        // ========================================
        'on' => [
            'clause' => SqlClause::FROM, // ON conditions are part of FROM clause
            'link' => SqlConditionLink::AND,
            'operator' => SqlOperator::ON,
        ],
        'andOn' => [
            'clause' => SqlClause::FROM,
            'link' => SqlConditionLink::AND,
            'operator' => SqlOperator::ON,
        ],
        'orOn' => [
            'clause' => SqlClause::FROM,
            'link' => SqlConditionLink::OR,
            'operator' => SqlOperator::ON,
        ],

        'onEqualTo' => [
            'clause' => SqlClause::FROM,
            'link' => SqlConditionLink::AND,
            'operator' => SqlOperator::EQUALS,
        ],
        'onNotEqualTo' => [
            'clause' => SqlClause::FROM,
            'link' => SqlConditionLink::AND,
            'operator' => SqlOperator::NOT_EQUALS,
        ],
        'onLessThan' => [
            'clause' => SqlClause::FROM,
            'link' => SqlConditionLink::AND,
            'operator' => SqlOperator::LESS_THAN,
        ],
        'onGreaterThan' => [
            'clause' => SqlClause::FROM,
            'link' => SqlConditionLink::AND,
            'operator' => SqlOperator::GREATER_THAN,
        ],
        'onLike' => [
            'clause' => SqlClause::FROM,
            'link' => SqlConditionLink::AND,
            'operator' => SqlOperator::LIKE,
        ],
        'onIn' => [
            'clause' => SqlClause::FROM,
            'link' => SqlConditionLink::AND,
            'operator' => SqlOperator::IN,
        ],
        'onBetween' => [
            'clause' => SqlClause::FROM,
            'link' => SqlConditionLink::AND,
            'operator' => SqlOperator::BETWEEN,
        ],
        'onNull' => [
            'clause' => SqlClause::FROM,
            'link' => SqlConditionLink::AND,
            'operator' => SqlOperator::IS_NULL,
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
            'clause' => SqlClause::SELECT, // Typically used in SELECT
            'link' => SqlConditionLink::AND,
            'operator' => SqlOperator::ADD,
        ],
        'subtract' => [
            'clause' => SqlClause::SELECT,
            'link' => SqlConditionLink::AND,
            'operator' => SqlOperator::SUBTRACT,
        ],
        'multiply' => [
            'clause' => SqlClause::SELECT,
            'link' => SqlConditionLink::AND,
            'operator' => SqlOperator::MULTIPLY,
        ],
        'divide' => [
            'clause' => SqlClause::SELECT,
            'link' => SqlConditionLink::AND,
            'operator' => SqlOperator::DIVIDE,
        ],
        'modulo' => [
            'clause' => SqlClause::SELECT,
            'link' => SqlConditionLink::AND,
            'operator' => SqlOperator::MODULO,
        ],

        'bitwiseAnd' => [
            'clause' => SqlClause::SELECT,
            'link' => SqlConditionLink::AND,
            'operator' => SqlOperator::BITWISE_AND,
        ],
        'bitwiseOr' => [
            'clause' => SqlClause::SELECT,
            'link' => SqlConditionLink::AND,
            'operator' => SqlOperator::BITWISE_OR,
        ],
        'bitwiseXor' => [
            'clause' => SqlClause::SELECT,
            'link' => SqlConditionLink::AND,
            'operator' => SqlOperator::BITWISE_XOR,
        ],
        'bitwiseNot' => [
            'clause' => SqlClause::SELECT,
            'link' => SqlConditionLink::AND,
            'operator' => SqlOperator::BITWISE_NOT,
        ],

        'concat' => [
            'clause' => SqlClause::SELECT,
            'link' => SqlConditionLink::AND,
            'operator' => SqlOperator::CONCAT,
        ],

        'jsonExtract' => [
            'clause' => SqlClause::SELECT,
            'link' => SqlConditionLink::AND,
            'operator' => SqlOperator::JSON_EXTRACT,
        ],
        'jsonExtractText' => [
            'clause' => SqlClause::SELECT,
            'link' => SqlConditionLink::AND,
            'operator' => SqlOperator::JSON_EXTRACT_TEXT,
        ],
        'jsonContains' => [
            'clause' => SqlClause::WHERE, // Can be used in WHERE conditions
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
        //Insert Clauses
        'into' => [
            'clause' => SqlClause::INTO,
        ],
        'values' => [
            'clause' => SqlClause::VALUES,
        ],
    ];

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
        return self::getMapping($method)['clause'];
    }

    /**
     * Get logical link for a method.
     */
    public static function getLogicalLink(string $method): SqlConditionLink
    {
        return self::getMapping($method)['link'];
    }

    /**
     * Get default operator for a method.
     */
    public static function getDefaultOperator(string $method): SqlOperator
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