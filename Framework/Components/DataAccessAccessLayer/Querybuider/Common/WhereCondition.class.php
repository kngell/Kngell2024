<?php

declare(strict_types=1);
enum WhereCondition : string
{
    case WHERE = 'where';
    case OR_WHERE = 'orWhere';
    case AND_WHERE = 'andWhere';
    case WHERE_EQUALS = 'whereEquals';
    case WHERE_GREATER_THAN = 'whereGreatterThan';
    case WHERE_EQUAL_OR_GREATER_THAN = 'whereEqualOrGreaterThan';
    case WHERE_LESS_THAN = 'whereLessThan';

    public static function exists(string $function) : bool
    {
        foreach (self::cases() as $case) {
            if ($case->value === $function) {
                return true;
            }
        }
        return false;
    }
}