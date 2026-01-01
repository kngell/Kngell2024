<?php

declare(strict_types=1);

class EntityMapper implements EntityMapperInterface
{
    /**
     * @var array<string, array>
     */
    private array $cachedFieldMaps = [];

    public function __construct()
    {
    }

    public function getTableName(Entity $entity): string
    {
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

    public function getEntityKeyProperty(Entity $entity): string|bool
    {
        $reflector = CustomReflection::getInstance($entity)->getObject();
        foreach ($reflector->getProperties(ReflectionProperty::IS_PRIVATE) as $property) {
            $attributes = $property->getAttributes();

            foreach ($attributes as $attribute) {
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
        $reflection = CustomReflection::getInstance($entity)->getObject();

        foreach ($reflection->getProperties(ReflectionProperty::IS_PRIVATE) as $property) {
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
            $reflection = CustomReflection::getInstance($entity)->getObject();
            return $reflection->getProperty($propertyName);
        } catch (ReflectionException $e) {
            return null;
        }
    }

    public function convertToPropertyName(string $fieldName): string
    {
        return lcfirst(StringUtils::studlyCaps($fieldName));
    }

    public function isPropertyInitialized(Entity $entity, string $field): bool
    {
        $reflector = CustomReflection::getInstance($entity)->getObject();
        $propertyName = StringUtils::studlyCaps($field);

        if (!$reflector->hasProperty($propertyName)) {
            $propertyName = lcfirst($propertyName);
            if (!$reflector->hasProperty($propertyName)) {
                return false;
            }
        }

        $property = $reflector->getProperty($propertyName);
        return $property->isInitialized($entity);
    }

    public function getPropertyValue(Entity $entity, string $field): mixed
    {
        $reflector = CustomReflection::getInstance($entity)->getObject();
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
        $reflection = CustomReflection::getInstance($entity)->getObject();
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
                if (!empty($args['currencyCode'])) {
                    if ($property->isInitialized($entity)) {
                        return $property->getValue($entity);
                    }
                }
            }
        }

        return null;
    }

    public function getCurrencyIdIfExists(Entity $entity): int|string|null
    {
        $reflection = CustomReflection::getInstance($entity)->getObject();

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
                if (!empty($args['currencyID'])) {
                    if ($property->isInitialized($entity)) {
                        return $property->getValue($entity);
                    }
                }
            }
        }

        return null;
    }

    public function hasProperty(Entity $entity, string $propertyName): bool
    {
        $reflection = CustomReflection::getInstance($entity)->getObject();
        return $reflection->hasProperty($propertyName);
    }

    public function isInitialized(Entity $entity, string $field): bool
    {
        $reflector = CustomReflection::getInstance($entity)->getObject();
        $property = $reflector->getProperty(StringUtils::camelCase($field));
        return $property->isInitialized($entity);
    }

    public function getFieldValue(Entity $entity, string $field): mixed
    {
        $reflector = CustomReflection::getInstance($entity)->getObject();

        foreach ($reflector->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            if (strtolower($method->getName()) === 'get' . strtolower($field)) {
                return $method->invoke($entity);
            }
        }

        return null;
    }

    public function getDatabaseFieldNameForProperty(Entity $entity, string $propertyName): string
    {
        $fieldToPropertyMap = $this->getFieldToPropertyMap($entity);

        $dbFieldName = array_search($propertyName, $fieldToPropertyMap, true);

        if ($dbFieldName !== false) {
            return $dbFieldName;
        }
        return StringUtils::camelCaseToSnakeCase($propertyName);
    }

    public function getAllProperties(Entity $entity): array
    {
        $properties = [];
        $reflection = CustomReflection::getInstance($entity)->getObject();

        foreach ($reflection->getProperties() as $prop) {
            $name = $prop->getName();
            $properties[$name] = $prop->isInitialized($entity)
                ? $prop->getValue($entity)
                : null;
        }

        return $properties;
    }

    private function isEntityFieldIdAttribute(string $attributeName): bool
    {
        $keyAttributes = ['EntityFieldId', 'Id', 'PrimaryKey', 'Key'];

        foreach ($keyAttributes as $keyAttr) {
            if (str_ends_with($attributeName, $keyAttr)) {
                return true;
            }
        }

        return false;
    }

    private function getDatabaseFieldForEntityKey(Entity $entity): string|bool
    {
        $reflector = CustomReflection::getInstance($entity)->getObject();
        foreach ($reflector->getProperties(ReflectionProperty::IS_PRIVATE) as $property) {
            $attributes = $property->getAttributes();

            foreach ($attributes as $attribute) {
                $attrName = $attribute->getName();
                $attrArguments = $attribute->getArguments();

                if ($this->isEntityFieldIdAttribute($attrName)) {
                    // 1. Check for explicit 'name' argument
                    if (isset($attrArguments['name'])) {
                        return $attrArguments['name'];
                    }

                    // 2. Check for 'dbField' argument
                    if (isset($attrArguments['dbField'])) {
                        return $attrArguments['dbField'];
                    }

                    // 3. Convert property name to snake_case
                    return StringUtils::studlyCapsToUnderscore($property->getName());
                }
            }
        }

        return false;
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
}