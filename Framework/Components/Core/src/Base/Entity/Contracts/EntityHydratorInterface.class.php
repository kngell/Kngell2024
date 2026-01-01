<?php

declare(strict_types=1);

interface EntityHydratorInterface
{
    /**
     * Hydrates the entity using raw data typically retrieved from a database (PDO fetch).
     *
     * @param Entity $entity The entity instance to hydrate.
     * @param array $data The raw database row data (snake_case keys).
     */
    public function pdoHydrate(Entity $entity, array $data): void;

    /**
     * Assigns data from a client request (e.g., POST data) to entity properties.
     * Handles naming conventions (camelCase to snake_case) and client-to-entity normalization.
     *
     * @param Entity $entity The entity instance to assign data to.
     * @param array $data The client data (usually snake_case or camelCase keys).
     */
    public function assign(Entity $entity, array $data): Entity;

    /**
     * Completes the primary entity hydration by processing any pending data
     * (e.g., data that arrived before relationship entities were ready).
     *
     * @param Entity $entity The entity instance holding the pending data.
     * @param array $pendingData A reference to the Entity's $pendingData array.
     * @param array $cachedFieldMap A reference to the Entity's $cachedFieldMap array.
     */
    public function completeMainHydration(Entity $entity, array &$pendingData, ?array &$cachedFieldMap): void;

    public function denormalizeAndSetProperty(Entity $entity, string $dbFieldName, mixed $rawValue): void;

    public function getChangeTracker(): ChangeTrackerInterface;

    public function getNormalizer(): TypeNormalizerInterface;

    public function getDirtyData(Entity $entity): array;
}