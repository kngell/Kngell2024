<?php

declare(strict_types=1);

interface ModelUtilityInterface
{
    public function processConditions(Entity $defaultEntity, mixed $params): ProcessedConditions;

    public function normalizeData(mixed $data, Entity $prototype): ModelOperationPayload;

    public function updateTimestamps(Entity|array|CollectionInterface $entity, ?DateTimeImmutable $at = null): void;
}