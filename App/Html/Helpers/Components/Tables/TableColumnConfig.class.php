<?php

declare(strict_types=1);

class TableColumnConfig
{
    public function __construct(
        public readonly string $key,
        public readonly string $label,
        public readonly TableCellType $cellType,
        public readonly bool $hasCheckbox = false,
        public readonly bool $hasDropdown = false,
        public readonly bool $sortable = false,
        public readonly string $hintText = '',
        public readonly string $ariaLabel = '',
    ) {
        $this->validate();
    }

    public function getCellTypeClass(): string
    {
        return "table__cell--{$this->cellType->value}";
    }

    public function getColClass(): string
    {
        return "table__col--{$this->cellType->value}";
    }

    private function validate(): void
    {
        if (empty($this->key)) {
            throw new InvalidArgumentException('Column key cannot be empty');
        }

        if (empty($this->label)) {
            throw new InvalidArgumentException(
                "Column '{$this->key}' must have a label",
            );
        }

        // Start columns must have checkbox in header
        if ($this->cellType === TableCellType::START && !$this->hasCheckbox) {
            throw new InvalidArgumentException(
                "Column '{$this->key}' with cellType 'start' must have hasCheckbox=true",
            );
        }

        // Only start supports checkbox
        if ($this->hasCheckbox && !$this->cellType->supportsHeaderCheckbox()) {
            throw new InvalidArgumentException(
                sprintf(
                    "Column '%s' with cellType '%s' cannot have hasCheckbox=true. "
                    . 'Only cellType "start" supports header checkboxes.',
                    $this->key,
                    $this->cellType->value,
                ),
            );
        }
    }

    public static function fromArray(array $data): self
    {
        $cellType = isset($data['cellType'])
            ? (
                $data['cellType'] instanceof TableCellType
                    ? $data['cellType']
                    : TableCellType::from((string) $data['cellType'])
            )
            : TableCellType::NORMAL;

        return new self(
            key:(string) ($data['key'] ?? ''),
            label:(string) ($data['label'] ?? ''),
            cellType:$cellType,
            hasCheckbox:(bool) ($data['hasCheckbox'] ?? false),
            hasDropdown:(bool) ($data['hasDropdown'] ?? false),
            sortable:(bool) ($data['sortable'] ?? false),
            hintText:(string) ($data['hintText'] ?? ''),
            ariaLabel:(string) ($data['ariaLabel'] ?? ''),
        );
    }

    /**
     * @param  array[] $columns
     *
     * @return self[]
     */
    public static function collection(array $columns): array
    {
        return array_map(
            fn (array $col) => self::fromArray($col),
            $columns,
        );
    }
}