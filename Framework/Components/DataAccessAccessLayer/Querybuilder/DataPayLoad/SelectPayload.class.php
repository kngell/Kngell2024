<?php

declare(strict_types=1);
final class SelectPayload implements SqlDataPayloadInterface
{
    public function __construct(
        private array $columns = [],
    ) {
    }

    /**
     * @return array<string>
     */
    public function getColumns(): array
    {
        return $this->columns;
    }
}