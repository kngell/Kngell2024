<?php

declare(strict_types=1);
class FileManager
{
    public static function get(string $directory, string $fileToSearch) : string|bool
    {
        $iterator = new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS);
        $ItSearch = new RecursiveIteratorIterator($iterator);
        foreach ($ItSearch as $file) {
            if ($file->isFile() && str_contains($file->getPathName(), $fileToSearch)) {
                return $file->getPathName();
            }
        }
        return false;
    }
}