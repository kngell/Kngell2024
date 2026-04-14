<?php

declare(strict_types=1);

interface RepositoryInterface
{
    public function create(): void;

    public function update(null|string|Closure $table = null, array $conditions = []): void;

    public function bulkUpdate(
        null|string|Closure $table = null,
        mixed $data = null,
        array $conditions = [],
        ?BulkUpdateType $type = null,
    ): void;

    public function delete(): void;

    public function findByID(int $id): void;

    public function findByIds(array $conditions = [], ?int $limit = null, ?int $offset = null, ?string $keyField = null): void;

    public function fetchIds(array $conditions = [], ?int $limit = null, ?int $offset = null, ?string $keyField = null): void;

    public function findAll(array $conditions = [], array $columns = []): void;

    public function findBy(array $conditions = [], ?int $limit = null, ?int $offset = null, array $columns = []): void;

    public function findOneBy(array $conditions = [], array $columns = []): void;

    public function showColumns(string $tableName): void;

    public function count(array $conditions): void;
}