<?php

declare(strict_types=1);

/**
 * SQL OPERATORS - Expression components used within clauses
 * These build conditions and expressions but are NOT clauses.
 */
enum Operator: string
{
    private const DEFAULT_OPS = 'EQUAL';
    private const array DEFAULTS_METHODS = [
        '=' => ['where', 'on', 'having', 'and', 'orWhere', 'andWhere'],
        '<>' => ['whereNotEqualTo', 'onNotEqualTo', 'havingNotEqualTo'],
        'IN' => ['whereIn', 'onIn', 'havingIn', 'in'],
        'NOT IN' => ['whereNotIn', 'onNotIn', 'havingNotIn', 'notIn'],
        '<' => ['whereLessThan', 'onLessThan', 'havingLessThan'],
        '>' => ['whereGreaterThan', 'onGreaterThen', 'havinGreaterThan'],
        '<=' => ['whereLessOrEqualTo', 'onLessOrEqualTo', 'havingLessOrEqualTo'],
        '>=' => ['whereGreaterOrEqualTo', 'onGreaterOrEqualTo', 'havingGreaterOrEqualTo'],
        'IS' => ['is'],
        'IS NOT' => ['isNot'],
    ];

    public static function exists(string $op): bool
    {
        foreach (self::cases() as $case) {
            if ($case->value === $op) {
                return true;
            }
        }
        return false;
    }

    public static function getOp(string $method): self|bool
    {
        foreach (self::DEFAULTS_METHODS as $op => $methodArr) {
            if (in_array($method, $methodArr)) {
                return self::from($op);
            }
        }
        return false;
    }
    // Logical
    case AND = 'AND';
    case OR = 'OR';

    // Comparison
    case EQUALS = '=';
    case NOT_EQUALS = '<>';
    case GREATER_THAN = '>';
    case LESS_THAN = '<';
    case GREATER_THAN_OR_EQUAL = '>=';
    case LESS_THAN_OR_EQUAL = '<=';
    case LIKE = 'LIKE';
    case NOT_LIKE = 'NOT LIKE';

    // Set
    case IN = 'IN';
    case NOT_IN = 'NOT IN';
    case BETWEEN = 'BETWEEN';
    case NOT_BETWEEN = 'NOT BETWEEN';

    // Null
    case IS_NULL = 'IS NULL';
    case IS_NOT_NULL = 'IS NOT NULL';
}