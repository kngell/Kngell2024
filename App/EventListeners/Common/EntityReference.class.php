<?php

declare(strict_types=1);

final class EntityReference
{
    public function __construct(
        public readonly string $type,
        public readonly int|string $id,
    ) {
    }

    public function __toString(): string
    {
        return "{$this->type}#{$this->id}";
    }
}