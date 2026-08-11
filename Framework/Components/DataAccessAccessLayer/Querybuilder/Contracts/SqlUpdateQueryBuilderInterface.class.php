<?php

declare(strict_types=1);
/**
 * UPDATE QUERY BUILDER - Data modification operations.
 */
interface SqlUpdateQueryBuilderInterface extends SqlQueryBuilderInterface, SqlCommonClauseInterface, SqlCommonConditionClauseInterface
{
    public function update(null|string|Closure $table = null): static;

    public function set(mixed ...$data): static;

    public function setColumn(string $column): static;

    public function setColumns(string ...$columns): static;

    public function setValue(mixed $value): static;

    public function setValues(mixed ...$values): static;

    public function innerJoin(mixed $query): static;

    public function bulkData(mixed $data): static;
}