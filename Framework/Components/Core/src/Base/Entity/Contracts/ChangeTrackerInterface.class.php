<?php

declare(strict_types=1);

interface ChangeTrackerInterface
{
    /**
     * Records the initial state of an entity.
     *
     * @param Entity $entity
     */
    public function track(Entity $entity): void;

    public function getChanges(Entity $entity): array;

    public function clear(Entity $entity): void;

    public function getOriginalData(Entity $entity): array;
}