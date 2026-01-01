<?php

declare(strict_types=1);

enum SqlConditionLink: string
{
    private const array LINKS_FAMILY = [
        'and' => [
            'where', 'andWhere', 'whereIn', 'whereNotIn',
            'whereEquals', 'whereNotEquals', 'whereLessThan', 'whereGreaterThan',
            'whereLessThanOrEqualTo', 'whereGreaterThanOrEqualTo',
        ],
        'or' => [
            'orWhere', 'orWhereIn', 'orWhereNotIn',
            'orWhereEquals', 'orWhereNotEquals', 'orWhereLessThan', 'orWhereGreaterThan',
            'orWhereLessThanOrEqualTo', 'orWhereGreaterThanOrEqualTo',
        ],
        'on' => ['on', 'andOn', 'orOn', 'onEqualTo', 'onNotEqualTo', 'onLessThan', 'onGreaterThan', 'onLike', 'onIn', 'onBetween', 'onNull'],
    ];

    public static function getFrom(string $method): self
    {
        foreach (self::LINKS_FAMILY as $linkType => $methods) {
            if (in_array($method, $methods)) {
                return self::from($linkType);
            }
        }

        // Default to AND for unknown methods
        return self::AND;
    }

    case AND = 'and';
    case OR = 'or';
    case ON = 'on';
}