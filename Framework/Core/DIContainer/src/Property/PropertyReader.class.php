<?php

declare(strict_types=1);

use Symfony\Component\Yaml\Yaml;

readonly class PropertyReader
{
    public function readProperties(string $filePath) : array
    {
        return Yaml::parse(file_get_contents($filePath));
    }
}