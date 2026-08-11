<?php

declare(strict_types=1);

interface DatabaseArrayFieldMappingInterface
{
    public function buildGroups(bool $isEdit, array|Entity $formValues): array;

    public function getBaseGroup(): array;

    public function getFieldMapping(array $variations): array;
}