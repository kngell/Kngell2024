<?php

declare(strict_types=1);

interface ChangeTrackerInterface
{
    /**
     * Records the initial state of an entity.
     *
     * @param object $entity
     */
    public function track(object $entity): void;

    /**
     * Computes and returns an associative array of changed properties.
     *
     * @param object $entity
     *
     * @return array<string, mixed> Associative array of [propertyName => currentValue]
     */
    public function getChanges(object $entity): array;

    /**
     * Clears tracking data for a specific entity (called after a successful persist/update).
     *
     * @param object $entity
     */
    public function clear(object $entity): void;
}