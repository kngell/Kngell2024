<?php

declare(strict_types=1);
/**
 * COMPOSITE INTERFACE - For builders that support multiple statement types.
 */
interface SqlCompositeQueryBuilderInterface
{
    public function select(string|array|Closure ...$columns): SqlSelectQueryBuilderInterface;

    public function with(string $cteTableName, SqlSelectQueryBuilderInterface|Closure $cteBody): SqlSelectQueryBuilderInterface;

    public function withRecursive(string $cteTableName, SqlSelectQueryBuilderInterface|Closure $cteBody): SqlSelectQueryBuilderInterface;

    public function selectWithAlias(string|array|Closure ...$columns): SqlSelectQueryBuilderInterface;

    public function insert(mixed ...$data): SqlInsertQueryBuilderInterface;

    public function update(null|string|Closure $table = null): SqlUpdateQueryBuilderInterface;

    public function delete(string $table): SqlDeleteQueryBuilderInterface;

    public function createTable(string $table): SqlDdlQueryBuilderInterface;

    public function getEntityManager(): EntityManagerInterface;

    public function getParameters(): array;

    public function getQuery(): string;

    public function reset(): void;
}