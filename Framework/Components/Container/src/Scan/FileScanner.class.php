<?php

declare(strict_types=1);
final readonly class FileScanner
{
    private function __construct()
    {
    }

    /**
     * @param string $namespace
     * @param string $directory
     * @param bool $recursive
     * @return ReflectionClass[]
     */
    public static function scanForClassesInNamespace(string $namespace, string $directory, bool $recursive) : array
    {
        $namespace = rtrim($namespace, '\\');

        $classes = [];

        foreach (new DirectoryIterator($directory) as $fileinfo) {
            if ($fileinfo->isDot()) {
                continue;
            }
            if ($recursive && $fileinfo->isDir()) {
                $baseName = $fileinfo->getBasename();
                $subNamespace = $namespace . '\\' . $baseName;
                $classes = array_merge($classes, self::scanForClassesInNamespace($subNamespace, $fileinfo->getRealPath(), true));
            } elseif ($fileinfo->isFile()) {
                $baseName = $fileinfo->getBasename('.php');
                $className = $namespace . '\\' . $baseName;
                try {
                    $classes[] = new ReflectionClass($className);
                } catch (ReflectionException $ex) {
                    //Nothing we can do skip it;
                }
            }
        }
        return $classes;
    }
}