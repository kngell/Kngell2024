<?php

declare(strict_types=1);

final class OnPayload implements SqlDataPayloadInterface
{
    public function __construct(
        private array $conditions = [],
    ) {
    }

    public function getConditions(): array
    {
        return $this->conditions;
    }

    public function toArray(): array
    {
        return $this->conditions;
    }

    public function isEmpty(): bool
    {
        return empty($this->conditions);
    }
}