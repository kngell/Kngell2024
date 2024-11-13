<?php

declare(strict_types=1);

final readonly class MappingFactory
{
    /**
     * @param string $autoload
     * @return ClassesMappingInterface
     */
    public static function create(string $autoload, array $basePath) : ClassesMappingInterface
    {
        return match ($autoload) {
            'psr4' => new Psr4ClassesMapping($basePath),
            'classmap' => new ClassmapMapping($basePath),
            default => throw new ServiceScannerException('Please specify the class mapping system: psr4 or classmap'),
        };
    }
}