<?php

declare(strict_types=1);

interface TypeNormalizerInterface
{
    /**
     * Converts a raw value from the **DATABASE** into the expected Entity property type.
     * Used for initial object hydration (pdoHydrate).
     */
    public function normalizeForDatabaseToEntity(mixed $rawValue, ReflectionProperty $property, object $entityInstance): mixed;

    /**
     * Converts a raw value from a **CLIENT/API** into the expected Entity property type.
     * Used for external data assignment (assign).
     */
    public function normalizeForClientToEntity(mixed $clientValue, ReflectionProperty $property, object $entityInstance): mixed;

    public function normalizeForEntityToDatabase(mixed $entityValue, ReflectionProperty $property): mixed;

    public function normalizeValuesForDatabase(array $values, object $entity): array;

    public function normalizeValueForDatabase(string $dbFieldName, mixed $rawValue, ?object $entity): mixed;
}