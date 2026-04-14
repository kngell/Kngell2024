<?php

declare(strict_types=1);
/**
 * SELECT QUERY BUILDER - Data retrieval operations.
 */
interface SqlSelectQueryBuilderInterface extends SqlQueryBuilderInterface
{
    // =============================================
    // CLAUSE CONSTRUCTION METHODS
    // =============================================
    public function select(string ...$columns): self;

    public function distinct(bool $enable = true): self;

    public function from(mixed $table, ?string $alias = null): self;

    public function groupBy(string ...$columns): self;

    public function having(mixed ...$conditions): self;

    public function orderBy(string ...$columnsDirections): self;

    public function limit(int $count): self;

    public function offset(int $count): self;

    public function withAlias(bool $withAlias = true): self;

    // ===========================================
    // JOIN CONSTRUCTION METHODS
    // ===========================================
    public function join(string|Closure $table, null|string|array $params = null): self;

    public function leftJoin(string|Closure $table, null|string|array $params = null): self;

    public function innerJoin(string|Closure $table, null|string|array $params = null): self;

    public function rightJoin(string|Closure $table, null|string|array $params = null): self;

    // ===========================================
    // WHERE CONDITIONS (SELECT-specific)
    // ===========================================
    public function where(mixed ...$conditions): self;

    public function whereEqualTo(string $column, mixed $value): self;

    public function whereNotEqualTo(string $column, mixed $value): self;

    public function whereLessThan(string $column, mixed $value): self;

    public function whereGreaterThan(string $column, mixed $value): self;

    public function whereLike(string $column, string $pattern): self;

    public function whereNotLike(string $column, string $pattern): self;

    public function whereIn(mixed ...$conditions): self;

    public function whereNotIn(mixed ...$conditions): self;

    public function whereNull(string $column): self;

    public function whereNotNull(string $column): self;

    public function whereBetween(string $column, mixed $min, mixed $max): self;

    // ===========================================
    // JOIN CONDITIONS
    // ===========================================
    public function on(mixed ...$onConditions): self;

    public function onEqualTo(string $leftColumn, string $rightColumn): self;

    public function onNotEqualTo(string $leftColumn, string $rightColumn): self;

    // ===========================================
    // LOGICAL COMBINATION
    // ===========================================
    public function andWhere(mixed ...$conditions): self;

    public function and(mixed ...$conditions): self;

    public function orWhere(mixed ...$conditions): self;

    public function or(mixed ...$conditions): self;

    // ===========================================
    // SET COMBINATION
    // ===========================================

    public function unionAll(SqlSelectQuery|Closure $query): self;
}