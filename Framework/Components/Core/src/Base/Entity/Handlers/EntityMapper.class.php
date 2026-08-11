<?php

declare(strict_types=1);

class EntityMapper implements EntityMapperInterface
{
    /** @var array<string, array> */
    private array $cachedFieldMaps = [];

    public function __construct()
    {
    }

    public function getTableName(Entity $entity, ?string $default = null): string
    {
        if ($default !== null) {
            return $default;
        }
        $reflection = $this->getReflector($entity);
        if ($reflection->hasProperty('defaultTableName')) {
            return $reflection->getProperty('defaultTableName')->getRawValue($entity);
        }
        $table = StringUtils::studlyCapsToUnderscore($entity::class);
        if (str_ends_with($table, '_show')) {
            return str_replace('_show', '', $table);
        }
        return $table;
    }

    public function getEntityKeyField(Entity $entity): string|bool
    {
        return $this->getDatabaseFieldForEntityKey($entity);
    }

    public function getFormat(Entity $entity): ?DisplayFormat
    {
        $keyField = $this->getEntityKeyField($entity);
        $property = $this->getPropertyForField($entity, $keyField);
        $attributes = $property->getAttributes(DisplayFormat::class);
        if (isset($attributes[0])) {
            return $attributes[0]?->newInstance();
        }
        return null;
    }

    public function getEntityKeyProperty(Entity $entity): string|bool
    {
        foreach ($this->getReflector($entity)->getProperties(ReflectionProperty::IS_PRIVATE) as $property) {
            foreach ($property->getAttributes() as $attribute) {
                if ($this->isEntityFieldIdAttribute($attribute->getName())) {
                    return $property->getName();
                }
            }
        }
        return false;
    }

    public function getFieldToPropertyMap(Entity $entity): array
    {
        $entityClass = $entity::class;
        if (isset($this->cachedFieldMaps[$entityClass])) {
            return $this->cachedFieldMaps[$entityClass];
        }

        $map = [];
        foreach ($this->getReflector($entity)->getProperties(ReflectionProperty::IS_PRIVATE) as $property) {
            $attributes = $property->getAttributes('EntityFieldId');
            $dbFieldName = $this->getDatabaseFieldName($property, $attributes);
            $map[$dbFieldName] = $property->getName();
        }

        return $this->cachedFieldMaps[$entityClass] = $map;
    }

    public function getPropertyForField(Entity $entity, string $dbFieldName): ?ReflectionProperty
    {
        $fieldMap = $this->getFieldToPropertyMap($entity);
        $propertyName = $fieldMap[$dbFieldName] ?? $this->convertToPropertyName($dbFieldName);

        try {
            return $this->getReflector($entity)->getProperty($propertyName);
        } catch (ReflectionException) {
            return null;
        }
    }

    public function convertToPropertyName(string $fieldName): string
    {
        return lcfirst(StringUtils::studlyCaps($fieldName));
    }

    public function unsetEntityPrimaryKey(Entity $entity): void
    {
        $primaryKeyField = $this->getEntityKeyField($entity);
        if ($primaryKeyField && $entity->entityKeyIsInitialzed()) {
            $this->getReflector($entity)->getProperty($primaryKeyField)->setValue($entity, null);
        }
    }

    public function isPropertyInitialized(Entity $entity, string $field): bool
    {
        $reflector = $this->getReflector($entity);
        $propertyName = StringUtils::studlyCaps($field);

        if (!$reflector->hasProperty($propertyName)) {
            $propertyName = lcfirst($propertyName);
            if (!$reflector->hasProperty($propertyName)) {
                return false;
            }
        }

        return $reflector->getProperty($propertyName)->isInitialized($entity);
    }

    public function getPropertyValue(Entity $entity, string $field): mixed
    {
        $reflector = $this->getReflector($entity);
        $field = StringUtils::studlyCaps($field);
        $getterName = 'get' . $field;

        foreach ($reflector->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            if (strtolower($method->getName()) === strtolower($getterName)) {
                return $method->invoke($entity);
            }
        }

        $propertyName = lcfirst($field);
        if ($reflector->hasProperty($propertyName)) {
            $property = $reflector->getProperty($propertyName);
            if ($property->isInitialized($entity)) {
                return $property->getValue($entity);
            }
        }

        return null;
    }

    public function getCurrencyCodeIfExists(Entity $entity): ?string
    {
        $reflection = $this->getReflector($entity);
        foreach (['currencyCode', 'currency_code', 'currency'] as $possibleName) {
            if ($reflection->hasProperty($possibleName)) {
                $property = $reflection->getProperty($possibleName);
                if ($property->isInitialized($entity)) {
                    return $property->getValue($entity);
                }
            }
        }

        foreach ($reflection->getProperties() as $property) {
            foreach ($property->getAttributes() as $attribute) {
                $args = $attribute->getArguments();
                if (!empty($args['currencyCode']) && $property->isInitialized($entity)) {
                    return $property->getValue($entity);
                }
            }
        }
        return null;
    }

    public function getCurrencyIdIfExists(Entity $entity): int|string|null
    {
        $reflection = $this->getReflector($entity);
        foreach (['currencyId', 'currencyID', 'currency_id'] as $possibleName) {
            if ($reflection->hasProperty($possibleName)) {
                $property = $reflection->getProperty($possibleName);
                if ($property->isInitialized($entity)) {
                    return $property->getValue($entity);
                }
            }
        }
        foreach ($reflection->getProperties() as $property) {
            foreach ($property->getAttributes() as $attribute) {
                $args = $attribute->getArguments();
                if (!empty($args['currencyID']) && $property->isInitialized($entity)) {
                    return $property->getValue($entity);
                }
            }
        }
        return null;
    }

    public function hasProperty(Entity $entity, string $propertyName): bool
    {
        $reflection = $this->getReflector($entity);
        if (StringUtils::isSnakeCase($propertyName)) {
            $propertyName = StringUtils::snakeCaseToCamelCase($propertyName);
        }
        return $reflection->hasProperty($propertyName);
    }

    public function isInitialized(Entity $entity, string $field): bool
    {
        $reflector = $this->getReflector($entity);
        $field = StringUtils::snakeCaseToCamelCase($field);
        if ($reflector->hasProperty($field)) {
            return $reflector->getProperty($field)->isInitialized($entity);
        }
        return false;
    }

    public function getFieldValue(Entity $entity, string $field): mixed
    {
        $reflector = $this->getReflector($entity);
        $fieldName = StringUtils::snakeCaseToCamelCase($field);
        foreach ($reflector->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            if (strtolower($method->getName()) === 'get' . strtolower($fieldName) && $entity->entityKeyIsInitialzed()) {
                return $method->invoke($entity);
            }
        }
        return null;
    }

    public function getDatabaseFieldNameForProperty(Entity $entity, string $propertyName): string
    {
        $fieldToPropertyMap = $this->getFieldToPropertyMap($entity);
        $dbFieldName = array_search($propertyName, $fieldToPropertyMap, true);
        return ($dbFieldName !== false) ? $dbFieldName : StringUtils::camelCaseToSnakeCase($propertyName);
    }

    public function getAllProperties(Entity $entity): array
    {
        $properties = [];
        $reflection = $this->getReflector($entity);
        foreach ($reflection->getProperties() as $prop) {
            $name = $prop->getName();
            $properties[$name] = $prop->isInitialized($entity) ? $prop->getValue($entity) : null;
        }
        return $properties;
    }

    private function getReflector(Entity $entity): ReflectionClass
    {
        return CustomReflection::getInstance($entity)->getClass();
    }

    private function isEntityFieldIdAttribute(string $attributeName): bool
    {
        foreach (['EntityFieldId', 'Id', 'PrimaryKey', 'Key'] as $keyAttr) {
            if (str_ends_with($attributeName, $keyAttr)) {
                return true;
            }
        }
        return false;
    }

    private function getDatabaseFieldForEntityKey(Entity $entity): string|bool
    {
        foreach ($this->getReflector($entity)->getProperties(ReflectionProperty::IS_PRIVATE) as $property) {
            foreach ($property->getAttributes() as $attribute) {
                if ($this->isEntityFieldIdAttribute($attribute->getName())) {
                    $args = $attribute->getArguments();
                    return $args['name'] ?? $args['dbField'] ?? StringUtils::studlyCapsToUnderscore($property->getName());
                }
            }
        }
        return false;
    }

    private function getDatabaseFieldName(ReflectionProperty $property, array $attributes): string
    {
        if (!empty($attributes)) {
            return $attributes[0]->getArguments()['name'] ?? StringUtils::studlyCapsToUnderscore($property->getName());
        }
        return StringUtils::studlyCapsToUnderscore($property->getName());
    }
}