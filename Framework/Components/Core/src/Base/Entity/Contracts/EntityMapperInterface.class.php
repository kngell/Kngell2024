<?php

declare(strict_types=1);

// Dependencies required for the EntityMapper

interface EntityMapperInterface
{
    public function getTableName(Entity $entity, ?string $default): string;

    public function getEntityKeyField(Entity $entity): string|bool;

    public function getEntityKeyProperty(Entity $entity): string|bool;

    public function getFieldToPropertyMap(Entity $entity): array;

    public function convertToPropertyName(string $fieldName): string;

    public function isPropertyInitialized(Entity $entity, string $field): bool;

    public function getPropertyValue(Entity $entity, string $field): mixed;

    public function getPropertyForField(Entity $entity, string $dbFieldName): ?ReflectionProperty;

    public function getCurrencyCodeIfExists(Entity $entity): ?string;

    public function getCurrencyIdIfExists(Entity $entity): int|string|null;

    public function isInitialized(Entity $entity, string $field): bool;

    public function getFieldValue(Entity $entity, string $field): mixed;

    public function getDatabaseFieldNameForProperty(Entity $entity, string $propertyName): string;

    public function hasProperty(Entity $entity, string $propertyName): bool;

    public function getAllProperties(Entity $entity): array;

    public function unsetEntityPrimaryKey(Entity $entity): void;
}