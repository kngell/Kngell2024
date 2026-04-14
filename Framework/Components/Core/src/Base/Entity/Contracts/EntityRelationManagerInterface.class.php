<?php

declare(strict_types=1);

interface EntityRelationManagerInterface
{
    public function resolveRealName(
        Entity $entity,
        string $name,
        array $tableAlias,
        array $tableMap,
        array $relationships,
    ): string;

    public function hasActiveRelationships(
        Entity $entity,
        array $tableAlias,
        array &$relatedEntities,
    ): bool;

    public function hydrateRelatedEntity(
        Entity $entity,
        string $dbRelationName,
        string $field,
        mixed $value,
        array $tableAlias,
        array $tableMap,
        array &$relatedEntities,
        ?array $relationshipConfig = null,
    ): void;

    public function completeRelatedEntityHydration(Entity $entity, array $relatedEntities): void;

    public function extractPrefixedData(array $data, string $prefix): array;

    public function setFactory(EntityFactoryInterface $factory): EntityRelationManager;

    public function hydrateManyRelated(
        Entity $parent,
        string $relationName,
        array $collectionData,
    ): void;

    public function resetCurrentPointers(array &$relatedEntities): void;
}
