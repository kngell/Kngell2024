<?php

declare(strict_types=1);

enum ConditionRuleType : string
{
    case WHERE = 'where';
    case IN = 'in';
    case ON = 'on';
    case SET = 'set';

    private const array RULE_TYPES = [
        'where' => ['where', 'orWhere', 'having', 'and', 'or', 'whereNotEqualTo'],
        'in' => ['whereIn', 'whereNotIn'],
        'on' => ['on', 'onNotIn', 'onLessThan', 'onGreaterThen'],
        'set' => ['set'],
    ];

    public static function getRuleType(string $method) : self
    {
        $statement = self::getFromValue($method);
        $case = self::from($statement->value);
        return $case ?? null;
    }

    private static function getFromValue(string $method): ?self
    {
        foreach (self::RULE_TYPES as $stValue => $family) {
            $family = ! is_array($family) ? [$family] : $family;
            if (in_array($method, $family)) {
                return self::from($stValue);
            }
        }
        return null;
    }
}