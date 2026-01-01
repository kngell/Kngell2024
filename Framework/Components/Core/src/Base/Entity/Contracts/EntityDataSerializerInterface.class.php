<?php

declare(strict_types=1);

interface EntityDataSerializerInterface
{
    public function getData(Entity $entity): array;

    public function restoreData(array $data): ?Entity;
}