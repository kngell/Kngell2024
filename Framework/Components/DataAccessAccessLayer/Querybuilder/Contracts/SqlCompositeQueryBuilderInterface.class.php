<?php

declare(strict_types=1);
/**
 * COMPOSITE INTERFACE - For builders that support multiple statement types.
 */
interface SqlCompositeQueryBuilderInterface
{
    public function select(string|array|Closure ...$columns): SqlSelectQueryBuilderInterface;

    public function insert(mixed ...$data): SqlInsertQueryBuilderInterface;

    public function update(string ...$columns): SqlUpdateQueryBuilderInterface;

    public function delete(string ...$columns): SqlDeleteQueryBuilderInterface;

    public function createTable(string $table): SqlDdlQueryBuilderInterface;

    public function getEntityManager(): EntityManagerInterface;

    public function getParameters(): array;

    public function getQuery(): string;
}