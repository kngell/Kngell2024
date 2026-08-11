<?php

declare(strict_types=1);
final class ProcessedConditions
{
    public function __construct(
        public readonly Entity $entity,
        public readonly array $conditions,
        public readonly ?string $deleteOption = null,
        public readonly ?DateTimeImmutable $archivedAt = null,
    ) {
    }
}