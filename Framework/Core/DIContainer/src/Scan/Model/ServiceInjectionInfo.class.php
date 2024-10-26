<?php

declare(strict_types=1);
readonly class ServiceInjectionInfo
{
    private ReflectionClass $class;
    private string|null $injectionName;
    private string $serviceAttributeClassName;
    private bool $primary;
    /**
     * @var ConstructorInjectionArg[]
     */
    private array $constructorArgs;
    private ReflectionMethod|null $beanMethod;
    private ReflectionNamedType|null $beanType;
    private string|null $configurationContainerKey;

    public function __construct(
        ReflectionClass $class,
        string|null $injectionName,
        string $serviceAttributeClassName,
        bool $primary,
        array $constructorArgs,
        ReflectionMethod|null $beanMethod,
        ReflectionNamedType|null $beanType,
        string|null $configurationContainerKey
    ) {
        $this->class = $class;
        $this->injectionName = $injectionName;
        $this->serviceAttributeClassName = $serviceAttributeClassName;
        $this->primary = $primary;
        $this->constructorArgs = $constructorArgs;
        $this->beanMethod = $beanMethod;
        $this->beanType = $beanType;
        $this->configurationContainerKey = $configurationContainerKey;
    }

    /**
     * Get the value of class.
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * Get the value of injectionName.
     */
    public function getInjectionName()
    {
        return $this->injectionName;
    }

    /**
     * Get the value of serviceAttributeClassName.
     */
    public function getServiceAttributeClassName()
    {
        return $this->serviceAttributeClassName;
    }

    /**
     * Get the value of pramary.
     */
    public function getPrimary()
    {
        return $this->primary;
    }

    /**
     * Get the value of constructorArgs.
     *
     * @return  ConstructorInjectionArg[]
     */
    public function getConstructorArgs()
    {
        return $this->constructorArgs;
    }

    /**
     * Get the value of beanMethod.
     */
    public function getBeanMethod()
    {
        return $this->beanMethod;
    }

    /**
     * Get the value of beanType.
     */
    public function getBeanType()
    {
        return $this->beanType;
    }

    /**
     * Get the value of configurationContainerKey.
     */
    public function getConfigurationContainerKey()
    {
        return $this->configurationContainerKey;
    }

    public function getContainerKey() : string
    {
        return $this->injectionName ?? $this->class->getName();
    }
}