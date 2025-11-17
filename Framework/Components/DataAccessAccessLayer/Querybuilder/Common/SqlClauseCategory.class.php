<?php

declare(strict_types=1);

/**
 * Clause Categories - Groups related SQL clauses.
 */
enum SqlClauseCategory: string
{
    /**
     * Get all methods that belong to this category.
     */
    public function getMethods(): array
    {
        return match($this) {
            self::SELECT => ['select'],
            self::FROM => array_merge(
                ['from'],
                // All JOIN methods including compound ones
                array_map(fn (SqlJoinType $type) => strtolower($type->name) . 'Join', SqlJoinType::cases()),
                array_map(fn (SqlJoinType $type) => 'inner' . strtolower($type->name) . 'Join', SqlJoinType::cases()),
                // ON conditions
                ['on', 'andOn', 'orOn', 'onClosure'],
            ),
            self::WHERE => [
                'where', 'andWhere', 'orWhere', 'whereIn', 'whereNotIn',
                'whereLike', 'whereNotLike', 'whereNull', 'whereNotNull',
                'whereBetween', 'whereNotBetween', 'whereExists', 'whereNotExists',
            ],
            self::GROUP_BY => ['groupBy'],
            self::HAVING => ['having', 'andHaving', 'orHaving'],
            self::ORDER_BY => ['orderBy'],
            self::LIMIT => ['limit'],
            self::OFFSET => ['offset'],
            self::INTO => ['insert', 'into', 'columns'],
            self::VALUES => ['values'],
            // ✅ Add default case to handle any missing cases
            default => []
        };
    }

    /**
     * Check if a method belongs to this category.
     */
    public function containsMethod(string $method): bool
    {
        return in_array($method, $this->getMethods());
    }

    public function toSqlClause(): SqlClause
    {
        return match ($this) {
            self::SELECT => SqlClause::SELECT,
            self::FROM => SqlClause::FROM,
            self::WHERE => SqlClause::WHERE,
            self::GROUP_BY => SqlClause::GROUP_BY,
            self::HAVING => SqlClause::HAVING,
            self::ORDER_BY => SqlClause::ORDER_BY,
            self::LIMIT => SqlClause::LIMIT,
            self::OFFSET => SqlClause::OFFSET,
        };
    }

    /**
     * Get category for a specific method.
     */
    public static function getCategoryForMethod(string $method): ?self
    {
        foreach (self::cases() as $category) {
            if ($category->containsMethod($method)) {
                return $category;
            }
        }
        return null;
    }
    case SELECT = 'select';
    case FROM = 'from';
    case WHERE = 'where';
    case GROUP_BY = 'groupBy';
    case HAVING = 'having';
    case ORDER_BY = 'orderBy';
    case LIMIT = 'limit';
    case OFFSET = 'offset';

    case INTO = 'into';
    case VALUES = 'values';
}