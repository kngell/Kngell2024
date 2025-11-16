<?php

declare(strict_types=1);

/**
 * JoinMethod - Utility for working with JOIN method names.
 */
final class JoinMethod
{
    public static function getAllMethods(): array
    {
        return array_map(
            fn (SqlJoinType $type) => strtolower($type->name) . 'Join',
            SqlJoinType::cases(),
        );
    }

    public static function isJoinMethod(string $method): bool
    {
        $normalized = strtolower($method);

        foreach (SqlJoinType::cases() as $joinType) {
            if ($normalized === strtolower($joinType->name) . 'join') {
                return true;
            }
        }

        return false;
    }

    public static function getJoinTypeFromMethod(string $method): ?SqlJoinType
    {
        $normalized = strtolower($method);

        foreach (SqlJoinType::cases() as $joinType) {
            $joinMethodName = strtolower($joinType->name) . 'join';
            if ($normalized === $joinMethodName) {
                return $joinType;
            }
        }

        return null;
    }

    public static function normalizeJoinClause(string $clause): string
    {
        return self::isJoinMethod($clause) ? 'join' : $clause;
    }
}