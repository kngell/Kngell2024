<?php

declare(strict_types=1);
final class UpdatePayload implements SqlDataPayloadInterface
{
    public function __construct(
        private array $updateData = [],
        private null|string $method = null,
    ) {
    }

    /**
     * @return array
     */
    public function getUpdateData(): array
    {
        return $this->updateData;
    }

    /**
     * @return null|string
     */
    public function getMethod(): ?string
    {
        return $this->method;
    }
}