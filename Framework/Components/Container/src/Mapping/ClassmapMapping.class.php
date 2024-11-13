<?php

declare(strict_types=1);

class ClassmapMapping implements ClassesMappingInterface
{
    /** @var string[] */
    private array $basePathToScan;

    private array $classmapping;

    private string $rootDir;

    public function __construct(array $basePathToScan)
    {
        $this->basePathToScan = $basePathToScan;
        $this->classmapping = require dirname(getcwd()) . '/vendor/composer/autoload_classmap.php';
        $this->rootDir = dirname(getcwd()) . DIRECTORY_SEPARATOR;
    }

    /**
     * @return ReflectionClass[]
     */
    public function getClassesToScan(): array
    {
        $classes = [];
        foreach ($this->basePathToScan() as $path) {
            $classes = array_merge($classes, $this->classesInthePath($path));
        }
        return $classes;
    }

    /**
     * @param string $path
     * @return ReflectionClass[]
     */
    private function classesInthePath(string $path) : array
    {
        $classes = [];
        foreach ($this->classmapping as $class => $directory) {
            $dir = substr($directory, strlen($this->rootDir));
            if (str_contains($path, '\\')) {
                throw new ServiceScannerException("The path $path sould not contains back slashes. Please provide a base directory to scan");
            }
            if (str_contains($class, '\\')) {
                continue;
            }
            if (str_starts_with($dir, $path)) {
                try {
                    $classes[] = new ReflectionClass($class);
                } catch (ReflectionException $ex) {
                    //Nothing we can do skip it;
                }
            }
        }
        return $classes;
    }

    /**
     * @return string[]
     */
    private function basePathToScan() : array
    {
        $paths = [];
        $rootPaths = [];
        foreach ($this->basePathToScan as $path) {
            $dir = explode(DIRECTORY_SEPARATOR, $path);
            if (! file_exists($this->rootDir . $dir[0]) || ! file_exists($this->rootDir . $path)) {
                throw new ServiceScannerException("The directory $path does not exist! Please provide a correct directory to scan");
            }
            if (! in_array($dir[0], $rootPaths)) {
                $rootPaths[] = $dir[0];
                $paths[] = $path;
            }
        }
        return $paths;
    }
}