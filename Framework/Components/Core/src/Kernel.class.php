<?php

declare(strict_types=1);
abstract class Kernel
{
    protected PropertyRegistry $propertyRegistry;
    protected ServiceContainerInterface $serviceContainer;
    protected bool $production = false;

    public function __construct()
    {
        $this->createPropertyRegistry();
        $this->production = $this->propertyRegistry->getPropertiesByNameOrDefault('production', false);

        $this->createContainer();
    }

    /**
     * @return string[]
     */
    abstract protected function getNamespacesToScan() : array;

    abstract protected function getPropertiesFilePath() : string;

    abstract protected function getRootDirectory() : string;

    abstract protected function getCacheDirectory() : string;

    abstract protected function autoloadMethod() : string;

    protected function createPropertyRegistry() : void
    {
        $reader = new PropertyReader();
        $properties = $reader->readProperties($this->getPropertiesFilePath());
        $properties['kngell-ecom']['rootDirectory'] = $this->getRootDirectory();
        $properties['kngell-ecom']['cacheDirectory'] = $this->getCacheDirectory();
        $this->propertyRegistry = new PropertyRegistry($properties);
    }

    protected function createContainer() : void
    {
        $scanner = new ServiceScanner($this->getNamespacesToScan(), $this->autoloadMethod());
        $scannedServices = $scanner->scan();
        $serviceCreator = new ServiceCreator($this->propertyRegistry);
        $beanMap = $serviceCreator->createServices($scannedServices);
        $this->serviceContainer = new ServiceContainer($beanMap);
    }
}