<?php

declare(strict_types=1);

interface TypeNormalizerInterface
{
    public function normalizeForDatabaseToEntity(mixed $rawValue, ReflectionProperty $property, Entity $entityInstance): mixed;

    public function normalizeForClientToEntity(mixed $clientValue, ReflectionProperty $property, Entity $entityInstance): mixed;

    public function normalizeForEntityToDatabase(mixed $entityValue, ReflectionProperty $property): mixed;

    public function normalizeValuesForDatabase(array $values, object $entity): array;

    public function normalizeValueForDatabase(string $dbFieldName, mixed $rawValue, ?Entity $entity): mixed;
}