<?php

declare(strict_types=1);
/**
 * UPDATE QUERY BUILDER - Data modification operations.
 */
interface SqlUpdateQueryBuilderInterface extends SqlQueryBuilderInterface
{
    public function update(string $table): self;

    public function set(array $data): self;

    public function setColumn(string $column, mixed $value): self;

    // UPDATE can have WHERE and JOINs
    public function where(mixed ...$conditions): self;

    public function whereEqualTo(string $column, mixed $value): self;

    public function andWhere(string $column, mixed $value): self;

    public function orWhere(string $column, mixed $value): self;

    public function join(string $table, ?string $alias = null): self;

    public function on(string $leftColumn, string $rightColumn): self;
}