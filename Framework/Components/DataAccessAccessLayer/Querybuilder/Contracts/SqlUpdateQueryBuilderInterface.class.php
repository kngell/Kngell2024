<?php

declare(strict_types=1);
/**
 * UPDATE QUERY BUILDER - Data modification operations.
 */
interface SqlUpdateQueryBuilderInterface extends SqlQueryBuilderInterface
{
    public function update(null|string|Closure $table = null): self;

    public function set(mixed ...$data): self;

    public function setColumn(string $column): self;

    public function setColumns(string ...$columns): self;

    public function setValue(mixed $value): self;

    public function setValues(mixed ...$values): self;

    public function where(mixed ...$conditions): self;

    public function whereEqualTo(string $column, mixed $value): self;

    public function andWhere(string $column, mixed $value): self;

    public function orWhere(string $column, mixed $value): self;

    public function join(string $table, ?string $alias = null): self;

    public function on(string $leftColumn, string $rightColumn): self;
}