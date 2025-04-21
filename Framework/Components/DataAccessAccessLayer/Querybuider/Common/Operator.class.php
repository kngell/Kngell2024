<?php

declare(strict_types=1);

enum Operator : string
{
    case EQUAL = '=';
    case NOT_EQUAL = '<>';
    case IN = 'in';
    case NOT_IN = 'not in';
    case LESS_THAN = '<';
    case GREATER_THAN = '>';
    case LESS_THAN_OR_EQUAL_TO = '<=';
    case GREATER_THAN_OR_EQUAL = '>=';

    private const DEFAULT_OPS = 'EQUAL';
    private const array DEFAULTS_METHODS = [
        '=' => ['where', 'on', 'having', 'and', 'orWhere'],
        '<>' => ['whereNotEqualTo', 'onNotEqualTo', 'havingNotEqualTo'],
        'in' => ['whereIn', 'onIn', 'havingIn', 'in'],
        'not in' => ['whereNotIn', 'onNotIn', 'havingNotIn', 'notIn'],
        '<' => ['whereLessThan', 'onLessThan', 'havingLessThan'],
        '>' => ['whereGreaterThan', 'onGreaterThen', 'havinGreaterThan'],
        '<=' => ['whereLessOrEqualTo', 'onLessOrEqualTo', 'havingLessOrEqualTo'],
        '>=' => ['whereGreaterOrEqualTo', 'onGreaterOrEqualTo', 'havingGreaterOrEqualTo'],
    ];

    public static function exists(string $op) : bool
    {
        foreach (self::cases() as $case) {
            if ($case->value === $op) {
                return true;
            }
        }
        return false;
    }

    public static function getOp(string $method) : self|bool
    {
        foreach (self::DEFAULTS_METHODS as $op => $methodArr) {
            if (in_array($method, $methodArr)) {
                return self::from($op);
            }
        }
        return false;
    }
}