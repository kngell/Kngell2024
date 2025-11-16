<?php

declare(strict_types=1);
class InsertData
{
    public function __construct(
        private array $columns,
        private array $values,
    ) {
        if (count($columns) !== count($values)) {
            throw new InvalidArgumentException('Columns and values count must match');
        }

        if (empty($columns)) {
            throw new InvalidArgumentException('Columns cannot be empty');
        }
    }

    public function getColumns(): array
    {
        return $this->columns;
    }

    public function getValues(): array
    {
        return $this->values;
    }
}