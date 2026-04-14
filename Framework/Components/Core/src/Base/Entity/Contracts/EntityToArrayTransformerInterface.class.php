<?php

declare(strict_types=1);

interface EntityToArrayTransformerInterface
{
    public function toArray(Entity $entity): array;

    public function toOriginalArray(Entity $entity): array;

    public function toDeepArray(
        Entity $entity,
        bool $includeRelationships = true,
        int $maxDepth = 2,
        array $excludedProperties = [],
    ): array;

    public function toFlattenedArray(
        Entity $entity,
        string $separator = '.',
        bool $includeRelationships = true,
        array $excludedProperties = [],
    ): array;

    public function toFormArray(
        Entity $entity,
        ?FormFieldMappingPayloadInterface $fieldMapping = null,
        bool $flattenNested = true,
        bool $formatValues = true,
    ): array;

    public function toDatabaseArray(
        Entity $entity,
        bool $includeRelationships = false,
    ): array;

    public function extractRelationshipIds(
        Entity $entity,
        array $relationshipNames = [],
    ): array;
}