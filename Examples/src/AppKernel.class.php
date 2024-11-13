<?php

declare(strict_types=1);
class AppKernel extends WebKernel
{
    protected function getNamespacesToScan(): array
    {
        return ['Examples', 'Framework/Starters'];
    }

    protected function getPropertiesFilePath(): string
    {
        return dirname(__DIR__) . '/Config/Properties.yaml';
    }

    protected function getRootDirectory(): string
    {
        return dirname(__DIR__);
    }

    protected function getCacheDirectory(): string
    {
        return $this->getRootDirectory() . '/Cache';
    }

    protected function autoloadMethod(): string
    {
        return 'classmap';
    }
}