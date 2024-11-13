<?php

declare(strict_types=1);

class ConfigurationScanner
{
    /**
     * @param ReflectionClass $class
     * @param string $configurationContainerKey
     * @return ServiceInjectionInfo[]
     */
    public function scanConfiguration(ReflectionClass $class, string $configurationContainerKey) : array
    {
        $beans = [];
        foreach ($class->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            $beanAttribute = $this->getBeanAttibute($method);
            if ($beanAttribute === null) {
                continue;
            }

            ReflectionTypeValidator::validateMethodReturnType($method);

            $builder = new ServiceInjectionInfoBuilder();
            $beans[] = $builder
                ->withClass($method->getDeclaringClass())
                ->withInjectionName($beanAttribute->name ?? $method->getName())
                ->withServiceAttributeClassName(Bean::class)
                ->withPrimary($this->hasPrimaryAttribute($method))
                ->withBeanMethod($method)
                ->withBeanType($method->getReturnType())
                ->withConfigurationContainerKey($configurationContainerKey)
                ->build();
        }
        return $beans;
    }

    private function getBeanAttibute(ReflectionMethod $method) : Bean|null
    {
        $attribute = $method->getAttributes(Bean::class, ReflectionAttribute::IS_INSTANCEOF);

        if (empty($attribute)) {
            return null;
        }

        return ArrayUtils::first($attribute)->newInstance();
    }

    private function hasPrimaryAttribute(ReflectionMethod $method) : bool
    {
        $primaryAttribute = $method->getAttributes(Primary::class, ReflectionAttribute::IS_INSTANCEOF);

        return count($primaryAttribute) > 0;
    }
}