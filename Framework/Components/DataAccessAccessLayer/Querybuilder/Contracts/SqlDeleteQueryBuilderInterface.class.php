<?php

declare(strict_types=1);
/**
 * DELETE QUERY BUILDER - Data deletion operations.
 */
interface SqlDeleteQueryBuilderInterface extends SqlQueryBuilderInterface
{
    public function delete(null|string|Closure $table = null, null|string $alias = null): self;

    public function from(null|string|Closure $table = null, ?string $alias = null): self;

    public function deleteFrom(string $table): self;

    public function where(mixed ...$conditions): self;

    public function whereEqualTo(string $column, mixed $value): self;

    public function andWhere(string $column, mixed $value): self;

    public function orWhere(string $column, mixed $value): self;

    public function join(string $table, ?string $alias = null): self;

    public function on(string $leftColumn, string $rightColumn): self;
}