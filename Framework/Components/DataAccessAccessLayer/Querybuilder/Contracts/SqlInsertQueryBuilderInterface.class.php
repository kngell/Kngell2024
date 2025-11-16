<?php

declare(strict_types=1);
/**
 * INSERT QUERY BUILDER - Data insertion operations.
 */
interface SqlInsertQueryBuilderInterface extends SqlQueryBuilderInterface
{
    public function insertInto(string $table): self;

    public function insert(mixed ...$data): self;

    public function into(string $table): self;

    public function values(mixed ...$data): self;

    public function columns(string|array ...$columns): self;

    public function fromSelect(SqlSelectQueryBuilderInterface $selectQuery): self;

    // INSERT-specific methods
    public function onDuplicateKeyUpdate(array $updates): self;

    public function ignore(): self;
}