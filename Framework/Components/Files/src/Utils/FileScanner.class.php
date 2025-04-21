<?php

declare(strict_types=1);

use DirectoryIterator;

final class FileScanner
{
    private function __construct()
    {
    }

    /**
     * Scan for Classes.
     *
     * @param string $namespace
     * @param string $directory
     * @param bool $recursive
     * @return array
     */
    public static function scanForClassesInNamespace(string $namespace, string $directory, bool $recursive = true) : array
    {
        $classes = [];
        $namespace = rtrim($namespace, '\\');
        foreach (new DirectoryIterator($directory) as $fileInfo) {
            if ($fileInfo->isDot()) {
                continue;
            }
            if ($recursive &&$fileInfo->isDir()) {
                $basename = $fileInfo->getBasename();
                $newNamespace = $namespace . '\\' . $basename;
                $classes = array_merge($classes, self::scanForClassesInNamespace($newNamespace, $fileInfo->getRealPath(), true));
            } elseif ($fileInfo->isFile()) {
                $basename = $fileInfo->getBasename('.php');
                $class = $namespace . '\\' . $basename;
                $classes[] = $class;
            }
        }
        return $classes;
    }
}