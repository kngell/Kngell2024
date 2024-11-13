<?php

declare(strict_types=1);

readonly class ServiceCreator
{
    private const int MAX_LOOP_COUNT = 255;
    private PropertyRegistry $propertyRegistry;

    /**
     * @param PropertyRegistry $propertyRegistry
     */
    public function __construct(PropertyRegistry $propertyRegistry)
    {
        $this->propertyRegistry = $propertyRegistry;
    }

    /**
     * @param ServiceInjectionInfo[] $scannedServices
     * @return Map<string,BeanInfo>
     */
    public function createServices(array $scannedServices) : Map
    {
        $beanMap = new Map();

        $this->createBeanMap($scannedServices, $beanMap);

        return $beanMap;
    }

    /**
     * @param ServiceInjectionInfo[] $scannedServices
     * @param Map $beanMap
     * @param array $cachedDependencies
     * @param int $loopCount
     * @return void
     */
    private function createBeanMap(
        array $scannedServices,
        Map &$beanMap,
        array $cachedDependencies = [],
        int $loopCount = 0
    ) : void {
        foreach ($scannedServices as $key => $scannedService) {
            $dependencies = $this->getDenpendencies($scannedService, $cachedDependencies, $scannedServices);
            if ($this->areAllDependenciesResolved($beanMap, $dependencies)) {
                $beanName = $scannedService->getContainerKey();

                if ($beanMap->has($beanName)) {
                    throw new BeanCreatorException("There's already a bean with the name : {$beanName}. All beans need to have unique names.");
                }

                $createdBean = BeanFactory::create($scannedService, $beanMap, $this->propertyRegistry);
                $beanMap->add($beanName, $createdBean);
                unset($scannedServices[$key]);
            }
        }
        if (! empty($scannedServices)) {
            $loopCount++;
            if ($loopCount > self::MAX_LOOP_COUNT) {
                throw new CircularDependencyException("There's a circular dependency: ", $scannedServices);
            }
            $this->createBeanMap($scannedServices, $beanMap, $cachedDependencies, $loopCount);
        }
    }

    private function areAllDependenciesResolved(Map $beanMap, array $dependencies) : bool
    {
        foreach ($dependencies as $dependency) {
            $found = false;
            /**
             * @var string $containerKey
             * @var BeanInfo $bean
             */
            foreach ($beanMap as $containerKey => $bean) {
                if ($containerKey === $dependency || in_array($dependency, $bean->getPossibleInjectionNames())) {
                    $found = true;
                    break;
                }
            }
            if (! $found) {
                return false;
            }
        }
        return true;
    }

    private function getDenpendencies(
        ServiceInjectionInfo $scannedService,
        array &$cachedDependencies,
        array $scannedServices
    ) : array {
        $containerKey = $scannedService->getContainerKey();
        if (array_key_exists($containerKey, $cachedDependencies)) {
            return $cachedDependencies[$containerKey];
        }

        $dependencies = [];

        foreach ($scannedService->getConstructorArgs() as $constructorArg) {
            if ($constructorArg->getType() === ConstructorInjectionArgType::PROPERTY) {
                continue;
            }

            if ($this->scannedServicesContainsServiceFromConstructorArg($constructorArg, $scannedServices)) {
                $dependencies[] = $constructorArg->getInjectionName();
            }
        }
        $cachedDependencies[$containerKey] = $dependencies;

        return $dependencies;
    }

    /**
     * @param ConstructorInjectionArg $constructorArg
     * @param ServiceInjectionInfo[] $scannedServices
     * @return bool
     */
    private function scannedServicesContainsServiceFromConstructorArg(
        ConstructorInjectionArg $constructorArg,
        array $scannedServices
    ) : bool {
        $type = $constructorArg->getParameter()->getType();
        if (! ($type instanceof ReflectionNamedType)) {
            //no support for multi Types. validate as well at scanning level
            return false;
        }
        foreach ($scannedServices as $scannedService) {
            if ($constructorArg->hasQualifier() && $constructorArg->getQualifier() === $scannedService->getInjectionName()) {
                return true;
            }

            if (
                $type->getName() === $scannedService->getClass()->getName() ||
            ($scannedService->getBeanType() !== null && $type->getName() === $scannedService->getBeanType()->getName())
            ) {
                return true;
            }
        }
        return false;
    }
}
