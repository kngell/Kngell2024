<?php

declare(strict_types=1);

interface FileSystemInterface
{
    public function exists(string $path): bool;

    public function isReadable(string $path): bool;

    public function isWritable(string $path): bool;

    public function getPermissions(string $path): int;
}