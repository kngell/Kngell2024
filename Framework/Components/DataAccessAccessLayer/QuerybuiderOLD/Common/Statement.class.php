<?php

declare(strict_types=1);

enum Statement : string
{
    case SELECT = 'select';
    case FROM = 'from';
    case JOIN = 'join';
    case LEFT_JOIN = 'leftJoin';
    case RIGTH_JOIN = 'rigthJoin';
    case INNER_JOIN = 'innerJoin';
    case ON = 'on';
    case WHERE = 'where';
    case HAVING = 'having';
    case GROUP_BY = 'groupBy';
    case ORDER_BY = 'orderBy';
    case LIMIT = 'limit';
    case OFFSET = 'offset';
    case UPDATE = 'update';
    case SET = 'set';
    case INSERT_INTO = 'insert';
    case VALUES = 'values';
    case DELETE = 'delete';

    private const array STATEMENT_FAMILY = [
        'select' => 'select',
        'from' => 'from',
        'join' => ['join', 'leftJoin', 'rigthJoin', 'innerJoin'],
        'where' => ['where',  'orWhere',  'whereNotIn', 'whereIn', 'andWhere', 'and', 'or', 'whereEquals', 'whereNotEquals', 'whereLessThan', 'whereGreaterThan', 'whereLessThanOrEqualTo', 'whereGreaterThanOrEqualTo',
        ],
        'on' => ['on', 'orOn', 'andOn', 'onNotIn', 'onIn', 'onEquals', 'onNotEquals',
        ],
        'having' => ['having', 'havingNotIn', 'orHaving', 'andHaving', 'havingIn', 'havingEquals', 'havingNotEquals',
        ],
        'groupBy' => 'groupBy',
        'orderBy' => 'orderBy',
        'limit' => 'limit',
        'offset' => 'offset',
        'update' => 'update',
        'set' => 'set',
        'insert' => 'insert',
        'values' => 'values',
        'delete' => 'delete',
    ];

    public static function getFromValue(string $method): ?self
    {
        foreach (self::STATEMENT_FAMILY as $stValue => $family) {
            $family = ! is_array($family) ? [$family] : $family;
            if (in_array($method, $family)) {
                return self::from($stValue);
            }
        }
        return null;
    }

    public static function exists(string $method) : bool
    {
        if (self::getFromValue($method) !== null) {
            return true;
        }
        return false;
    }

    public static function isCondition(string $method) : bool
    {
        $case = self::getFromValue($method);
        if ($case && in_array($case->value, ['where', 'on', 'having'])) {
            return true;
        }
        return false;
    }

    public static function allMethods() : array
    {
        $fm = [];
        foreach (self::STATEMENT_FAMILY as $family) {
            $fm[] = array_merge($fm, $family);
        }
        return $fm;
    }

    public static function getFamily(string $method): array
    {
        $case = self::getFromValue($method);
        return self::STATEMENT_FAMILY[$case->value] ?? [];
    }
}
