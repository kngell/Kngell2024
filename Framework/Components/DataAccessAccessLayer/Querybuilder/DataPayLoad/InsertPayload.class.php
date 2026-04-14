<?php

declare(strict_types=1);

final class InsertPayload implements SqlDataPayloadInterface
{
    /**
     * @param array<int, string> $columns
     * @param array<int, array<int, mixed>> $values 2D array: each row is a sequential array of values
     */
    public function __construct(
        private array $columns = [],
        private array $values = [],
    ) {
    }

    public function getData(): ?array
    {
        if (empty($this->columns) && empty($this->values)) {
            return null;
        }
        return [
            'columns' => $this->getColumns(),
            'values' => $this->getValues(),
        ];
    }

    /**
     * Get columns to insert.
     *
     * @return array<int, string>
     */
    public function getColumns(): array
    {
        return $this->columns;
    }

    /**
     * Get values to insert.
     *
     * @return array<int, array<int, mixed>>
     */
    public function getValues(): array
    {
        return $this->values;
    }

    /**
     * Returns number of rows.
     */
    public function rowCount(): int
    {
        return count($this->values);
    }

    /**
     * Returns true if payload has no values yet.
     */
    public function isEmpty(): bool
    {
        return empty($this->values);
    }

    public function hasColumns(): bool
    {
        return !empty($this->columns);
    }

    public function hasValues(): bool
    {
        return !empty($this->values);
    }
}
