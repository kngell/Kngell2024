<?php

declare(strict_types=1);

enum ConditionRuleType: string
{
    private const array RULE_TYPES = [
        'where' => ['where', 'orWhere', 'having', 'whereNotEqualTo', 'andWhere'],
        'in' => ['whereIn', 'whereNotIn', 'orWhereIn', 'orWhereNotIn'],
        'on' => ['on', 'onNotIn', 'onLessThan', 'onGreaterThen', 'onIn'],
        'set' => ['set'],
        'insert' => ['values'],
    ];

    public function toSqlClause(): SqlClause
    {
        return match ($this) {
            self::WHERE => SqlClause::WHERE,
            self::ON => SqlClause::ON,
            self::SET => SqlClause::SET,
            self::INSERT => SqlClause::VALUES,
            self::IN => SqlClause::WHERE,
        };
    }

    public function getSupportedMethods(): array
    {
        return self::RULE_TYPES[$this->value] ?? [];
    }

    public static function getRuleType(string $method): ?self
    {
        return self::getFromValue($method);
    }

    public static function getFromMethod(string $method): ?self
    {
        return self::getFromValue($method);
    }

    private static function getFromValue(string $method): ?self
    {
        foreach (self::RULE_TYPES as $stValue => $family) {
            if (in_array($method, $family)) {
                return self::from($stValue);
            }
        }
        return null;
    }

    case WHERE = 'where';
    case IN = 'in';
    case ON = 'on';
    case SET = 'set';
    case INSERT = 'insert';
}