<?php

declare(strict_types=1);

interface ModelUtilityInterface
{
    public function processConditions(Entity $defaultEntity, mixed $params): array;

    public function normalizeData(mixed $data, Entity $prototype): ModelOperationPayload;

    public function updateTimestamps(Entity|array $entity): void;
}