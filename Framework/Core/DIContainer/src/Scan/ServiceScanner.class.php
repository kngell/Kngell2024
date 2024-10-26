<?php

declare(strict_types=1);
class ServiceScanner
{
    private ConfigurationScanner $configurationScanner;
    private ClassesMappingInterface $mapping;

    /**
     * @param array $baseNamespaceToScan
     * @param string $autoload
     */
    public function __construct(array $baseNamespaceToScan, string $autoload)
    {
        $this->configurationScanner = new ConfigurationScanner();
        $this->mapping = MappingFactory::create($autoload, $baseNamespaceToScan);
    }

    /**
     * @return ServiceInjectionInfo[]
     */
    public function scan() : array
    {
        $services = [];
        foreach ($this->mapping->getClassesToScan() as $class) {
            $serviceAttributes = $class->getAttributes(Service::class, ReflectionAttribute::IS_INSTANCEOF);
            $serviceAttributesCount = count($serviceAttributes);
            if ($serviceAttributesCount === 0) {
                continue;
            }
            if ($serviceAttributesCount > 1) {
                throw new ServiceScannerException("The service '{$class->getName()}' has multiples Services Attributes. A class can only have one");
            }
            /** @var ReflectionAttribute $serviceAttribute */
            $serviceAttribute = ArrayUtils::first($serviceAttributes);
            $serviceInjectionInfo = $this->buildServiceInjectionInfos($class, $serviceAttribute);
            $services[] = $serviceInjectionInfo;

            if ($serviceAttribute->getName() === Configuration::class) {
                $services = array_merge($services, $this->configurationScanner->scanConfiguration($class, $serviceInjectionInfo->getContainerKey()));
            }
        }

        return $services;
    }

    private function buildServiceInjectionInfos(ReflectionClass $class, ReflectionAttribute $serviceAttribute) : ServiceInjectionInfo
    {
        $builder = new ServiceInjectionInfoBuilder();

        return $builder
            ->withClass($class)
            ->withInjectionName($this->getInjectionName($serviceAttribute))
            ->withServiceAttributeClassName($serviceAttribute->getName())
            ->withPrimary($this->hasPrimaryAttribute($class))
            ->withConstructorArgs($this->getConstructorInjectionArgs($class))
            ->build();
    }

    private function getInjectionName(ReflectionAttribute $serviceAttribute) : string|null
    {
        /** @var Service $service */
        $service = $serviceAttribute->newInstance();
        if (! StringUtils::isBlanc($service->name)) {
            return $service->name;
        }
        return null;
    }

    private function hasPrimaryAttribute(ReflectionClass $class) : bool
    {
        $primaryAttribute = $class->getAttributes(Primary::class, ReflectionAttribute::IS_INSTANCEOF);

        return count($primaryAttribute) > 0;
    }

    /**
     * @param ReflectionClass $class
     * @return ConstructorInjectionArg[]
     */
    private function getConstructorInjectionArgs(ReflectionClass $class) : array
    {
        $constructor = $class->getConstructor();
        if ($constructor === null) {
            return [];
        }
        $args = [];
        foreach ($constructor->getParameters() as $parameter) {
            ReflectionTypeValidator::validateConstructorParameterType($parameter, $class->getName());
            $args[] = $this->buildConstructorArgs($parameter);
        }
        return $args;
    }

    private function buildConstructorArgs(ReflectionParameter $parameter) : ConstructorInjectionArg
    {
        $builder = new ConstructionInjectionArgsBuilder();

        return $builder
            ->withParameter($parameter)
            ->withType($this->getConstrutorInjectionArgType($parameter))
            ->withQualifier($this->getConstructorInjectionArgQualifier($parameter))
            ->build();
    }

    private function getConstrutorInjectionArgType(ReflectionParameter $parameter) : ConstructorInjectionArgType
    {
        $propertyAttribute = $parameter->getAttributes(Property::class, ReflectionAttribute::IS_INSTANCEOF);
        if (empty($propertyAttribute)) {
            return ConstructorInjectionArgType::BEAN;
        }
        return ConstructorInjectionArgType::PROPERTY;
    }

    private function getConstructorInjectionArgQualifier(ReflectionParameter $parameter) : string|null
    {
        $qualifierAttribute = $parameter->getAttributes(Qualifier::class, ReflectionAttribute::IS_INSTANCEOF);

        if (empty($qualifierAttribute)) {
            return null;
        }
        return ArrayUtils::first($qualifierAttribute)->newInstance()->name;
    }
}