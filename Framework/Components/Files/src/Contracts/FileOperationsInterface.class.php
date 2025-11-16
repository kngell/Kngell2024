<?php

declare(strict_types=1);
interface FileOperationsInterface
{
    public function copy(string $source, string $destination): void;

    public function move(string $source, string $destination): void;

    public function delete(string $path): void;

    public function getSize(string $path): int;

    public function getChecksum(string $path, string $algorithm = 'md5'): string;

    public function touch(string $path, ?int $time = null, ?int $atime = null): void;
}