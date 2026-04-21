<?php

declare(strict_types=1);

class ObfuscationService
{
    /** @var array<string, array<string, array>> */
    private array $propertyMapCache = [];

    /** @var array<string, ReflectionClass> */
    private array $reflectionCache = [];

    public function __construct(
        private TypePresenterFactory $presenterFactory,
    ) {
    }

    public function prepareForSave(array $data, string $entityClass): array
    {
        $propertyMap = $this->getPropertyMap($entityClass);

        if (empty($propertyMap)) {
            return $data;
        }

        $processed = $data;

        foreach ($propertyMap as $field => $config) {
            $propertyInfo = $config;

            if (!$propertyInfo['shouldObfuscate'] || !isset($data[$field])) {
                continue;
            }
            $value = $data[$field];
            $processed[$field] = $this->processField($value, $propertyInfo, $entityClass);
        }

        return $processed;
    }

    public function deobfuscateId(string $obfuscatedId, string $entityClass): ?int
    {
        $propertyMap = $this->getPropertyMap($entityClass);

        foreach ($propertyMap as $propertyInfo) {
            if (!$propertyInfo['shouldObfuscate']) {
                continue;
            }

            $result = $this->processField($obfuscatedId, $propertyInfo, $entityClass);

            if (is_int($result)) {
                return $result;
            }
        }

        return null;
    }

    private function processField(string $value, array $propertyInfo, string $entityClass): mixed
    {
        /** @var ReflectionProperty $property */
        $property = $propertyInfo['property'];

        $presenter = $this->presenterFactory->getPresenterForValue($value, $property);

        if (!$presenter instanceof ObfuscatedPresenter) {
            return $value;
        }

        static $contextCache = [];
        if (!isset($contextCache[$entityClass])) {
            $contextCache[$entityClass] = (new ReflectionClass($entityClass))->newInstanceWithoutConstructor();
        }

        return $presenter->normalizeForEntity(
            $value,
            $property,
            $contextCache[$entityClass],
        );
    }

    private function getPropertyMap(string $entityClass): array
    {
        if (isset($this->propertyMapCache[$entityClass])) {
            return $this->propertyMapCache[$entityClass];
        }

        $reflection = $this->getReflectionClass($entityClass);
        $propertyMap = [];

        foreach ($reflection->getProperties() as $property) {
            $fieldName = $this->getFieldNameFromProperty($property);

            if ($fieldName === null) {
                continue;
            }

            $propertyMap[$fieldName] = [
                'property' => $property,
                'shouldObfuscate' => $this->shouldObfuscate($property),
            ];
        }
        $this->propertyMapCache[$entityClass] = $propertyMap;

        return $propertyMap;
    }

    private function getFieldNameFromProperty(ReflectionProperty $property): ?string
    {
        $attributes = $property->getAttributes(EntityFieldId::class);

        foreach ($attributes as $attribute) {
            return $attribute->newInstance()->getName();
        }

        return null;
    }

    private function shouldObfuscate(ReflectionProperty $property): bool
    {
        $attributes = $property->getAttributes(DisplayFormat::class);

        foreach ($attributes as $attribute) {
            $format = $attribute->newInstance();
            return $format->obfuscate === true;
        }

        return false;
    }

    private function getReflectionClass(string $entityClass): ReflectionClass
    {
        if (!isset($this->reflectionCache[$entityClass])) {
            $this->reflectionCache[$entityClass] = new ReflectionClass($entityClass);
        }

        return $this->reflectionCache[$entityClass];
    }
}