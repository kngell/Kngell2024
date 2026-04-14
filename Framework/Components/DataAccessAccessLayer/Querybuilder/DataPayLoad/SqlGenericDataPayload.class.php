<?php

declare(strict_types=1);

class SqlGenericDataPayload implements SqlDataPayloadInterface
{
    public function __construct(
        private array $data = [],
        private null|string $method = null,
    ) {
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @return null|string
     */
    public function getMethod(): ?string
    {
        return $this->method;
    }
}