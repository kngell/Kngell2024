<?php

declare(strict_types=1);

enum SqlMethodCategory: string
{
    public function getMethods(): array
    {
        return match($this) {
            self::UPDATE => ['update', 'bulkUpdate'],
            self::DELETE => ['delete'],
            self::INSERT => ['insert'],
            self::SELECT => ['select', 'unionAll'],

            self::FROM => array_merge(
                ['from'],
                array_map(fn (SqlJoinType $type) => strtolower($type->name) . 'Join', SqlJoinType::cases()),
                array_map(fn (SqlJoinType $type) => 'inner' . strtolower($type->name) . 'Join', SqlJoinType::cases()),
                array_map(fn (SqlJoinType $type) => strtolower($type->name), SqlJoinType::cases()),
                // ON conditions
                ['on', 'andOn', 'orOn', 'onClosure'],
                //delete method
                ['deleteFrom'],
                ['bulkData'],
            ),
            self::SET => ['set', 'setColumn', 'setColumns', 'setValue', 'setValues'],
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
            self::INTO => ['into', 'columns'],
            self::VALUES => ['values'],
            self::WITH => ['with', 'withRecursive', 'addCte', 'cycle'],

            // ✅ Add default case to handle any missing cases
            default => []
        };
    }

    public function isInitial(): bool
    {
        return $this === self::SELECT ||
        $this === self::UPDATE ||
        $this === self::INSERT
        || $this === self::DELETE;
    }

    /**
     * Check if a method belongs to this category.
     */
    public function containsMethod(string $method): bool
    {
        return in_array($method, $this->getMethods());
    }

    public function toSqlClause(): SqlClause|SqlCteClause
    {
        return match ($this) {
            self::WITH => SqlCteClause::WITH,
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

    public function toSqlClauseForRules(): SqlClause
    {
        return match ($this) {
            self::SELECT => SqlClause::WHERE,
            self::FROM => SqlClause::WHERE,
            self::WHERE => SqlClause::WHERE,
            self::GROUP_BY => SqlClause::GROUP_BY,
            self::HAVING => SqlClause::WHERE,
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
    case UPDATE = 'update';
    case DELETE = 'delete';
    case INSERT = 'insert';
    case WITH = 'with';
    case SELECT = 'select';
    case FROM = 'from';
    case WHERE = 'where';
    case GROUP_BY = 'groupBy';
    case HAVING = 'having';
    case ORDER_BY = 'orderBy';
    case LIMIT = 'limit';
    case OFFSET = 'offset';

    case SET = 'set';

    case INTO = 'into';
    case VALUES = 'values';
    case CYCLE = 'cycle';
}