<?php

declare(strict_types=1);

class Psr4ClassesMapping implements ClassesMappingInterface
{
    /** @var string[] */
    private array $baseNamespacesToScan;

    private array $psr4Mapping;

    public function __construct(array $baseNamespaceToScan)
    {
        $this->baseNamespacesToScan = $baseNamespaceToScan;
        $this->psr4Mapping = require dirname(getcwd()) . '/vendor/composer/autoload_psr4.php';
    }

    /**
     * @return ReflectionClass[]
     */
    public function getClassesToScan(): array
    {
        $classes = [];
        foreach ($this->baseNamespacesToScan as $namespace) {
            $classes = array_merge($classes, $this->scanNamespace($namespace));
        }
        return $classes;
    }

    /**
     * @param string $namespace
     * @return ReflectionClass[]
     */
    private function scanNamespace(string $namespace) : array
    {
        $classes = [];
        foreach ($this->findStartingDirectory($namespace) as $directory) {
            $classes = array_merge(
                $classes,
                FileScanner::scanForClassesInNamespace($namespace, $directory, true)
            );
        }
        return $classes;
    }

    /**
     * @param string $namespace
     * @return string[]
     */
    private function findStartingDirectory(string $namespace) : array
    {
        $namespace = StringUtils::addTrailingString($namespace, '\\');

        //Namspace matching completely
        if (array_key_exists($namespace, $this->psr4Mapping)) {
            return $this->psr4Mapping[$namespace];
        }

        //Find parent psr4 and append the path
        foreach ($this->psr4Mapping as $key => $directories) {
            if (! str_starts_with($namespace, $key)) {
                continue;
            }

            $pathToAppend = StringUtils::addBeginningString(ltrim(rtrim($namespace, '\\'), $key), DIRECTORY_SEPARATOR);

            return array_map(static fn ($directory) => $directory . $pathToAppend, $directories);
        }
        throw new ServiceScannerException("Could not find any Psr4 Mapping for namespace '{$namespace}'. Is there a typo?");
    }
}