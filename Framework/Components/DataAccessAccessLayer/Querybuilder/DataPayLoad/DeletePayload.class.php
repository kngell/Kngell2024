<?php

declare(strict_types=1);
final class DeletePayload implements SqlDataPayloadInterface
{
    public function __construct(
        private array $map = [],
        private null|string $method = null,
    ) {
    }

    /**
     * @return array
     */
    public function getDeleteData(): array
    {
        return $this->map['delete'] ?? [];
    }

    public function getFromData(): array
    {
        return $this->map['from'] ?? [];
    }

    public function getWhereData(): array
    {
        return $this->map['where'] ?? [];
    }

    public function getMap(): array
    {
        return $this->map;
    }

    public function getTable(): array
    {
        return $this->map['from']['table'];
    }

    /**
     * @return null|string
     */
    public function getMethod(): ?string
    {
        return $this->method;
    }
}