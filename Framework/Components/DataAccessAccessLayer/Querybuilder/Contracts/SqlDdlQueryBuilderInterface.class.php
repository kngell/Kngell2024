<?php

declare(strict_types=1);
/**
 * DDL QUERY BUILDER - Schema operations.
 */
interface SqlDdlQueryBuilderInterface extends SqlQueryBuilderInterface
{
    public function createTable(string $table): self;

    public function dropTable(string $table): self;

    public function alterTable(string $table): self;

    public function truncateTable(string $table): self;

    public function addColumn(string $column, string $type): self;

    public function dropColumn(string $column): self;

    public function modifyColumn(string $column, string $newType): self;

    public function addPrimaryKey(string ...$columns): self;

    public function addForeignKey(string $column, string $referencesTable, string $referencesColumn): self;

    public function addIndex(string ...$columns): self;
}