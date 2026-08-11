<?php

declare(strict_types=1);
/**
 * SELECT QUERY BUILDER - Data retrieval operations.
 */
interface SqlSelectQueryBuilderInterface extends SqlQueryBuilderInterface, SqlCommonClauseInterface, SqlCommonConditionClauseInterface
{
    // =============================================
    // CLAUSE CONSTRUCTION METHODS
    // =============================================
    public function select(string ...$columns): static;

    public function distinct(bool $enable = true): static;

    public function distinctCount(bool $enable = true): static;

    public function groupBy(string ...$columns): static;

    public function having(mixed ...$conditions): static;

    public function orderBy(mixed ...$columnsDirections): static;

    public function limit(int $count): static;

    public function offset(int $count): static;

    public function withAlias(bool $withAlias = true): static;

    // ===========================================
    // JOIN CONSTRUCTION METHODS
    // ===========================================
    public function join(string|Closure $table, null|string|array $params = null): static;

    public function leftJoin(string|Closure $table, null|string|array $params = null): static;

    public function innerJoin(string|Closure $table, null|string|array $params = null): static;

    public function rightJoin(string|Closure $table, null|string|array $params = null): static;

    // ===========================================
    // SET COMBINATION
    // ===========================================

    public function unionAll(SqlSelectQuery|Closure $query): static;
}