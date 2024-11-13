<?php

declare(strict_types=1);
class ServiceInjectionInfoBuilder
{
    private ReflectionClass $class;
    private string|null $injectionName = null;
    private string $serviceAttributeClassName;
    private bool $primary = false;
    /**
     * @var ConstructorInjectionArg[]
     */
    private array $constructorArgs = [];
    private ReflectionMethod|null $beanMethod = null;
    private ReflectionNamedType|null $beanType = null;
    private string|null $configurationContainerKey = null;

    public function withClass(ReflectionClass $class): self
    {
        $this->class = $class;
        return $this;
    }

    public function withInjectionName(?string $injectionName): self
    {
        $this->injectionName = $injectionName;
        return $this;
    }

    public function withServiceAttributeClassName(string $serviceAttributeClassName): self
    {
        $this->serviceAttributeClassName = $serviceAttributeClassName;
        return $this;
    }

    public function withPrimary(bool $primary): self
    {
        $this->primary = $primary;
        return $this;
    }

    public function withConstructorArgs(array $constructorArgs): self
    {
        $this->constructorArgs = $constructorArgs;
        return $this;
    }

    public function withBeanMethod(?ReflectionMethod $beanMethod): self
    {
        $this->beanMethod = $beanMethod;
        return $this;
    }

    public function withBeanType(?ReflectionNamedType $beanType): self
    {
        $this->beanType = $beanType;
        return $this;
    }

    public function withConfigurationContainerKey(?string $configurationContainerKey): self
    {
        $this->configurationContainerKey = $configurationContainerKey;
        return $this;
    }

    public function build() : ServiceInjectionInfo
    {
        return new ServiceInjectionInfo(
            $this->class,
            $this->injectionName,
            $this->serviceAttributeClassName,
            $this->primary,
            $this->constructorArgs,
            $this->beanMethod,
            $this->beanType,
            $this->configurationContainerKey
        );
    }
}