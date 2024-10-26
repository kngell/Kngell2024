<?php

declare(strict_types=1);

readonly class BeanInfo
{
    private string $attributeType;
    private string|null $injectionName;
    private bool $isPrimary;
    /**
     * @var string[]
     */
    private array $possibleInjectionNames;
    private string $containerKey;

    private object $service;

    /**
     * @param string $attributeType
     * @param string|null $injectionName
     * @param bool $isPrimary
     * @param string $containerKey
     * @param object $service
     */
    public function __construct(
        string $attributeType,
        ?string $injectionName,
        bool $isPrimary,
        string $containerKey,
        object $service
    ) {
        $this->attributeType = $attributeType;
        $this->injectionName = $injectionName;
        $this->isPrimary = $isPrimary;
        $this->containerKey = $containerKey;
        $this->service = $service;
        $this->possibleInjectionNames = $this->initPossibleInjectionNames();
    }

    /**
     * @return string
     */
    public function getAttributeType(): string
    {
        return $this->attributeType;
    }

    /**
     * @return string|null
     */
    public function getInjectionName(): ?string
    {
        return $this->injectionName;
    }

    /**
     * @return bool
     */
    public function isPrimary(): bool
    {
        return $this->isPrimary;
    }

    /**
     * @return array
     */
    public function getPossibleInjectionNames(): array
    {
        return $this->possibleInjectionNames;
    }

    /**
     * @return string
     */
    public function getContainerKey(): string
    {
        return $this->containerKey;
    }

    /**
     * @return object
     */
    public function getService(): object
    {
        return $this->service;
    }

    /**
     * @return string[]
     */
    private function initPossibleInjectionNames() : array
    {
        $class = new ReflectionClass($this->service);
        return array_merge(
            [$class->getName()],
            $this->getAllParentClassName($class),
            $class->getInterfaceNames()
        );
    }

    /**
     * @param ReflectionClass $class
     * @param string[] $classes
     * @return string[]
     */
    private function getAllParentClassName(ReflectionClass $class, array $classes = []) : array
    {
        $parent = $class->getParentClass();
        if ($parent !== false) {
            $classes = array_merge($classes, $this->getAllParentClassName($parent, $classes));
        }
        return $classes;
    }
}