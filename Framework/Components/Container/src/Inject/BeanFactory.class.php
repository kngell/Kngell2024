<?php

declare(strict_types=1);

final readonly class BeanFactory
{
    private function __construct()
    {
    }

    /**
     * @param ServiceInjectionInfo $injectionInfo
     * @param Map $beanMap
     * @param PropertyRegistry $propertyRegistry
     * @return BeanInfo
     */
    public static function create(ServiceInjectionInfo $injectionInfo, Map $beanMap, PropertyRegistry $propertyRegistry) : BeanInfo
    {
        try {
            if ($injectionInfo->getBeanMethod() !== null) {
                $object = $injectionInfo->getBeanMethod()->invoke(
                    $beanMap->get($injectionInfo->getConfigurationContainerKey())->getService()
                );
            } else {
                $constructorArgs = [];
                foreach ($injectionInfo->getConstructorArgs() as $constructorArg) {
                    $constructorArgs[] = self::resolveConstructorArg(
                        $constructorArg,
                        $beanMap,
                        $propertyRegistry,
                        $injectionInfo->getClass()->getName()
                    );
                }
                $object = empty($constructorArg) ? $injectionInfo->getClass()->newInstance() : $injectionInfo->getClass()->newInstance(...$constructorArgs);
            }
            return new BeanInfo(
                $injectionInfo->getServiceAttributeClassName(),
                $injectionInfo->getInjectionName(),
                $injectionInfo->isPrimary(),
                $injectionInfo->getContainerKey(),
                $object
            );
        } catch (ReflectionException $ex) {
            throw new BeanCreatorException("Got a reflection exception that is unrecoverable: {$ex->getMessage()}", 0, $ex);
        }
    }

    private static function resolveConstructorArg(
        ConstructorInjectionArg $constructorArg,
        Map $beanMap,
        PropertyRegistry $propertyRegistry,
        string $serviceClassName
    ) : mixed {
        if ($constructorArg->getType() === ConstructorInjectionArgType::PROPERTY) {
            return self::resolveProperty(
                $constructorArg,
                $propertyRegistry,
                $serviceClassName
            );
        }
        return self::resolveBean($constructorArg, $beanMap, $serviceClassName);
    }

    private static function resolveProperty(
        ConstructorInjectionArg $constructorArg,
        PropertyRegistry $propertyRegistry,
        string $serviceClassName
    ) : string|int|float|bool|array|null {
        $propName = $constructorArg->getPropertyName();

        try {
            return $propertyRegistry->getPropertiesByName($propName);
        } catch (PropertyNotFoundException $ex) {
            if ($constructorArg->hasDefaultValue()) {
                return $constructorArg->getParameter()->getDefaultValue();
            }
            if ($constructorArg->allowsNull()) {
                return null;
            }

            throw new BeanCreatorException("Could not find property: {$propName} for injection into {$serviceClassName}");
        }
    }

    private static function resolveBean(
        ConstructorInjectionArg $constructorArg,
        Map $beanMap,
        string $serviceClassName
    ) : mixed {
        $possibleInjections = [];

        /**
         * @var string $containerKey
         * @var BeanInfo $bean
         */
        foreach ($beanMap->getAll() as $containerKey => $bean) {
            // find existing bean with qualifier
            if (
                $constructorArg->hasQualifier() && $bean->getInjectionName() !== null && $bean->getInjectionName() === $constructorArg->getQualifier()
            ) {
                return $bean->getService();
            }

            //Find existing bean base on a class name
            if ($constructorArg->getParameterClassName() === $containerKey) {
                return $bean->getService();
            }

            if (in_array($constructorArg->getParameterClassName(), $bean->getPossibleInjectionNames())) {
                $possibleInjections[] = $bean;
            }
        }
        if (! empty($possibleInjections)) {
            $count = count($possibleInjections);
            if ($count === 1) {
                return $possibleInjections[0]->getService();
            }
            //find Primary

            foreach ($possibleInjections as $possibleInjection) {
                if ($possibleInjection->isPrimary()) {
                    return $possibleInjection->getService();
                }
            }

            throw new BeanCreatorException("we found {$count} possible injections for the class with type {$constructorArg->getParameterClassName()}. Did you forget to specify the primary attribute?");
        }

        if ($constructorArg->hasDefaultValue()) {
            return $constructorArg->getParameter()->getDefaultValue();
        }
        if ($constructorArg->allowsNull()) {
            return null;
        }
        if ($constructorArg->hasQualifier()) {
            throw new BeanCreatorException("Cannot inject class with the name: {$constructorArg->getQualifier()} into {$serviceClassName}. Did you specify a service with this name?");
        } else {
            throw new BeanCreatorException("Cannot inject class with the name: {$constructorArg->getParameterClassName()} into {$serviceClassName}. Did you add a service attribute to this class?");
        }
    }
}
