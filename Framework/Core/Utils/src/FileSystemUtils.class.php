<?php

declare(strict_types=1);
final readonly class FileSystemUtils
{
    private function __construct()
    {
    }

    public static function createDir(string $path, int $permission = 0777) : void
    {
        if (is_dir($path)) {
            return;
        }
        mkdir($path, $permission, true);
    }

    public static function fileExists(string $filePath) : bool
    {
        return file_exists($filePath);
    }
}
