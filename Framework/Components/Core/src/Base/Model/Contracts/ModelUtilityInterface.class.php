<?php

declare(strict_types=1);

interface ModelUtilityInterface
{
    public function processConditions(Entity $defaultEntity, mixed $params): array;

    public function prepareForSave(Entity $defaultEntity, mixed $data): Entity|array|CollectionInterface;

    public function updateTimestamps(Entity|array $entity): void;
}