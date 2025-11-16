<?php

declare(strict_types=1);

interface DirectoryInterface
{
    public function create(string $path, int $permissions = 0755): void;

    public function list(string $path, bool $recursive = false): array;

    public function delete(string $path): void;

    public function isEmpty(string $path): bool;

    public function getFileCount(string $path): int;

    public function getDirectoryCount(string $path): int;
}