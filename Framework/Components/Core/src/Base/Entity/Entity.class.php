<?php

declare(strict_types=1);

use Brick\Money\Money;

abstract class Entity
{
    protected const string DATE_FORMAT = 'Y-m-d H:i:s';
    protected const array RELATIONSHIPS = [];

    private array $tableAlias = [];
    private array $tableMap = [];
    private array $relatedEntities = [];
    private array $pendingData = [];
    private ?array $cachedFieldMap = null;

    public function __construct(
        array $tableAlias,
        array $tableMap,
        private TypeNormalizerInterface $normalizer,
        private ChangeTrackerInterface $changeTracker,
    ) {
        $this->tableAlias = $tableAlias;
        $this->tableMap = $tableMap;
    }

    public function __set(string $name, mixed $value): void
    {
        $name = $this->realName($name);

        if (str_contains($name, '.')) {
            [$relationName, $field] = explode('.', $name, 2);

            if (isset(static::RELATIONSHIPS[$relationName])) {
                $this->hydrateRelatedEntity($relationName, $field, $value);
                return;
            }
        }

        if ($this->hasActiveRelationships()) {
            $this->pendingData[$name] = $value;
        } else {
            $this->denormalizeAndSetProperty($name, $value);
        }
    }

    public function assign(array $data): self
    {
        $reflection = CustomReflection::getInstance($this)->getObject();

        $properties = $reflection->getProperties();

        foreach ($data as $key => $value) {
            $propertyName = StringUtils::camelCase($key);
            $property = $this->findPropertyByName($properties, $propertyName);

            if ($property) {
                $setterMethod = 'set' . ucfirst($propertyName);
                $convertedValue = $this->normalizer->normalizeForClientToEntity($value, $property, $this);

                if ($reflection->hasMethod($setterMethod)) {
                    $method = $reflection->getMethod($setterMethod);
                    $method->invoke($this, $convertedValue);
                } else {
                    $property->setAccessible(true);
                    $property->setValue($this, $convertedValue);
                }
            }
        }
        return $this;
    }

    public function pdoHydrate(array $data): void
    {
        foreach ($data as $dbFieldName => $rawValue) {
            $this->denormalizeAndSetProperty($dbFieldName, $rawValue);
        }
        $this->changeTracker->track($this);
    }

    public function getDirtyData(): array
    {
        $changes = $this->changeTracker->getChanges($this);
        $normalizedData = [];
        foreach ($changes as $property => $value) {
            // Find ReflectionProperty for $property
            // ...
            // **IMPLIED CHANGE:** Use the entity-to-database method
            // $normalizedData[$dbField] = $this->normalizer->normalizeForEntityToDatabase($value, $property);
        }

        return $normalizedData;
    }

    public function hydrateWithRelations(): void
    {
        $this->completeHydration();
    }

    public function completeHydration(): void
    {
        if (!empty($this->pendingData) || !empty($this->relatedEntities)) {
            $this->completeMainHydration();
            $this->hydrateRelatedEntities();
            $this->changeTracker->track($this);
        }
    }

    public function completeMainHydration(): void
    {
        if (!empty($this->pendingData)) {
            if ($this->cachedFieldMap === null) {
                $this->cachedFieldMap = $this->buildFieldToPropertyMap();
            }

            foreach ($this->pendingData as $key => $value) {
                // $key here is the DB field name (e.g., 'user_name')
                $this->denormalizeAndSetProperty($key, $value);
            }

            $this->pendingData = [];
        }
    }

    /**
     * Optionally set createdAt and updatedAt.
     */
    public function touchTimestamps(): void
    {
        if ($this instanceof TimestampableInterface) {
            $now = new DateTimeImmutable();

            if (method_exists($this, 'setCreatedAt')) {
                $this->setCreatedAt($now);
            }

            if (method_exists($this, 'setUpdatedAt')) {
                $this->setUpdatedAt($now);
            }
        }
    }

    /**
     * Optionally soft-delete entity.
     */
    public function touchDeleted(): void
    {
        if ($this instanceof SoftDeletableInterface && method_exists($this, 'softDelete')) {
            $this->softDelete();
        }
    }

    public function table(): string
    {
        return StringUtils::studlyCapsToUnderscore(static::class);
    }

    public function toOriginalArray(): array
    {
        $array = [];
        $reflection = CustomReflection::getInstance($this)->getObject();

        foreach ($reflection->getProperties() as $prop) {
            $name = StringUtils::studlyCapsToUnderscore($prop->getName());
            $array[$name] = $prop->getValue($this);
        }

        return $array;
    }

    public function toArray(): array
    {
        $array = [];
        $reflection = CustomReflection::getInstance($this)->getObject();

        foreach ($reflection->getProperties(ReflectionProperty::IS_PRIVATE) as $property) {
            $property->setAccessible(true);
            $propertyName = $property->getName();
            if ($property->isInitialized($this)) {
                $array[$propertyName] = $property->getValue($this);
            }

            // $array[$propertyName] = $value;

            // $array[$propertyName] = $this->convertValueForArray($value);
        }

        return $array;
    }

    public function isEmpty(): bool
    {
        $data = $this->toArray();

        foreach ($data as $value) {
            if ($value !== null && $value !== '' && $value !== []) {
                return false;
            }
        }

        return true;
    }

    public function getCurrencyCodeIfExists(): ?string
    {
        $reflection = CustomReflection::getInstance($this)->getObject();

        // Check for common naming variations
        foreach (['currencyCode', 'currency_code', 'currency'] as $possibleName) {
            if ($reflection->hasProperty($possibleName)) {
                $property = $reflection->getProperty($possibleName);
                $property->setAccessible(true);
                return $property->getValue($this);
            }
        }

        // Also allow an attribute to mark a property as currencyCode
        foreach ($reflection->getProperties() as $property) {
            foreach ($property->getAttributes() as $attribute) {
                $args = $attribute->getArguments();
                if (!empty($args['currencyCode'])) {
                    $property->setAccessible(true);
                    return $property->getValue($this);
                }
            }
        }

        return null;
    }

    public function getCurrencyIdIfExists(): int|string|null
    {
        $reflection = CustomReflection::getInstance($this)->getObject();

        foreach (['currencyId', 'currencyID', 'currency_id'] as $possibleName) {
            if ($reflection->hasProperty($possibleName)) {
                $property = $reflection->getProperty($possibleName);
                $property->setAccessible(true);
                return $property->getValue($this);
            }
        }

        foreach ($reflection->getProperties() as $property) {
            foreach ($property->getAttributes() as $attribute) {
                $args = $attribute->getArguments();
                if (!empty($args['currencyID'])) {
                    $property->setAccessible(true);
                    return $property->getValue($this);
                }
            }
        }

        return null;
    }

    public function getEntityKeyField(): string|bool
    {
        $reflector = CustomReflection::getInstance($this)->getObject();

        foreach ($reflector->getProperties(ReflectionProperty::IS_PRIVATE) as $property) {
            $identifier = $property->getAttributes();
            if (!empty($identifier)) {
                $attribute = ArrayUtils::first($identifier);
                $attrArguments = $attribute->getArguments();

                return $attrArguments['name'] ?? StringUtils::studlyCapsToUnderscore($property->getName());
            }
        }

        return false;
    }

    public function isInitialized(string $field): bool
    {
        $reflector = CustomReflection::getInstance($this)->getObject();
        $property = $reflector->getProperty(StringUtils::studlyCaps($field));
        return $property->isInitialized($this);
    }

    public function getFieldValue(string $field): mixed
    {
        $reflector = CustomReflection::getInstance($this)->getObject();

        foreach ($reflector->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            if (strtolower($method->getName()) === 'get' . strtolower($field)) {
                return $method->invoke($this);
            }
        }

        return null;
    }

    public function setTableAlias(array $tableAlias): self
    {
        $this->tableAlias = $tableAlias;
        return $this;
    }

    /**
     * Extract data with specific prefix and remove the prefix.
     */
    protected function extractPrefixedData(array $data, string $prefix): array
    {
        $result = [];
        $prefixLength = strlen($prefix);

        foreach ($data as $key => $value) {
            if (str_starts_with($key, $prefix)) {
                $cleanKey = substr($key, $prefixLength);
                $result[$cleanKey] = $value;
            }
        }

        return $result;
    }

    protected function convertArrayValues(array $array): array
    {
        return array_map([$this, 'convertValueForArray'], $array);
    }

    private function hasActiveRelationships(): bool
    {
        if (!empty($this->relatedEntities)) {
            return true;
        }
        foreach ($this->tableAlias as $relation => $alias) {
            $baseRelation = $this->getBaseTable($relation);
            if (isset(static::RELATIONSHIPS[$baseRelation])) {
                return true;
            }
        }
        return false;
    }
    // private function hasActiveRelationships(): bool
    // {
    //     if (!empty($this->relatedEntities)) {
    //         return true;
    //     }

    //     foreach ($this->tableAlias as $relation => $alias) {
    //         $relation = $this->getBaseTable($relation);
    //         $relation = StringUtils::camelCase($relation);
    //         if (isset(static::RELATIONSHIPS[$relation])) {
    //             return true;
    //         }
    //     }

    //     return false;
    // }

    private function getBaseTable(string $logicalTable): string
    {
        if (preg_match('/^(.+)_join_\d+$/', $logicalTable, $matches)) {
            return $matches[1];
        }

        return $logicalTable;
    }

    private function realName(string $name): string
    {
        if (empty($this->tableAlias)) {
            return $name;
        }

        $logicalToPhysicalMap = $this->tableMap;

        foreach ($this->tableAlias as $logicalKey => $alias) {
            if (str_starts_with($name, $alias . '_')) {
                $physicalTable = $logicalToPhysicalMap[$logicalKey] ?? $logicalKey;

                if (isset(static::RELATIONSHIPS[$physicalTable])) {
                    $fieldName = substr($name, strlen($alias) + 1);
                    return $physicalTable . '.' . $fieldName;
                }
                return substr($name, strlen($alias) + 1);
            }
        }
        return $name;
    }

    private function buildFieldToPropertyMap(): array
    {
        $map = [];
        $reflection = CustomReflection::getInstance($this)->getObject();

        foreach ($reflection->getProperties(ReflectionProperty::IS_PRIVATE) as $property) {
            $attributes = $property->getAttributes(EntityFieldId::class);
            $dbFieldName = $this->getDatabaseFieldName($property, $attributes);
            $map[$dbFieldName] = $property->getName();
        }

        return $map;
    }

    private function denormalizeAndSetProperty(string $dbFieldName, mixed $rawValue): void
    {
        if ($this->cachedFieldMap === null) {
            $this->cachedFieldMap = $this->buildFieldToPropertyMap();
        }
        $fieldToPropertyMap = $this->cachedFieldMap;
        $reflection = CustomReflection::getInstance($this)->getObject();

        $propertyName = $fieldToPropertyMap[$dbFieldName] ?? $this->convertToPropertyName($dbFieldName);

        if (property_exists($this, $propertyName)) {
            try {
                $property = $reflection->getProperty($propertyName);

                $convertedValue = $this->normalizer->normalizeForDatabaseToEntity(
                    rawValue: $rawValue,
                    property: $property,
                    entityInstance: $this,
                );

                // 3. Set the property value
                $this->setPropertyValue($propertyName, $convertedValue);
            } catch (ReflectionException $e) {
                // Property exists but reflection failed (highly unlikely in this flow)
                error_log("Failed to reflect property {$propertyName}: {$e->getMessage()}");
            }
        }
    }

    private function setPropertyValue(string $propertyName, $value): void
    {
        $reflection = CustomReflection::getInstance($this)->getObject();
        try {
            $property = $reflection->getProperty($propertyName);
            $property->setAccessible(true);
            $property->setValue($this, $value);
        } catch (ReflectionException $e) {
            // Handle case where property doesn't exist (shouldn't happen here)
        }
    }

    private function convertToPropertyName(string $fieldName): string
    {
        return lcfirst(StringUtils::studlyCaps($fieldName));
    }

    private function getDatabaseFieldName(ReflectionProperty $property, array $attributes): string
    {
        if (!empty($attributes)) {
            $attribute = $attributes[0];
            $arguments = $attribute->getArguments();
            return $arguments['name'] ?? StringUtils::studlyCapsToUnderscore($property->getName());
        }

        return StringUtils::studlyCapsToUnderscore($property->getName());
    }

    private function convertValueForArray($value): mixed
    {
        if ($value instanceof self) {
            return $value->toArray();
        }
        if ($value instanceof DateTimeInterface) {
            return $value->format(self::DATE_FORMAT);
        }
        if ($value instanceof Money) {
            return $value->getAmount();
        }
        if (is_array($value)) {
            return $this->convertArrayValues($value);
        }
        if (is_object($value) && method_exists($value, '__toString')) {
            return (string) $value;
        } else {
            return $value;
        }
    }

    private function hydrateRelatedEntity(string $relationName, string $field, mixed $value): void
    {
        $entityClass = static::RELATIONSHIPS[$relationName];

        if (!array_key_exists($relationName, $this->relatedEntities)) {
            $this->relatedEntities[$relationName] = new $entityClass($this->tableAlias, $this->tableMap, $this->normalizer, $this->changeTracker);
        }

        $this->relatedEntities[$relationName]->__set($field, $value);
    }

    private function hydrateRelatedEntities(): void
    {
        foreach ($this->relatedEntities as $relationName => $entity) {
            if (method_exists($entity, 'completeHydration')) {
                $entity->completeHydration();
            }

            $propertyName = $relationName;
            if (property_exists($this, $propertyName)) {
                $this->$propertyName = $entity;
            }
        }
    }

    private function findPropertyByName(array $properties, string $propertyName): ?ReflectionProperty
    {
        foreach ($properties as $property) {
            if ($property->getName() === $propertyName) {
                return $property;
            }
        }
        return null;
    }

    private function processValueForAssignment(ReflectionProperty $property, mixed $value): mixed
    {
        $attributes = $property->getAttributes();
        if (!empty($attributes)) {
            $attr = ArrayUtils::first($attributes);
            if ($attr->getName() === 'DateField' && is_string($value)) {
                return $this->formatDateString($value);
            }
        }
        return $value;
    }

    private function formatDateString(string $dateString): string
    {
        $date = new DateTimeImmutable($dateString);
        return $date->format(self::DATE_FORMAT);
    }
}