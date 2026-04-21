<?php

declare(strict_types=1);

final class FieldMappingPayload implements FormFieldMappingPayloadInterface
{
    public function __construct(
        private array $fieldMapping = [],
        private array $numericFields = [],
    ) {
    }

    public function isEmpty(): bool
    {
        return empty($this->fieldMapping);
    }

    /**
     * @return array
     */
    public function getFieldMapping(): array
    {
        return $this->fieldMapping;
    }

    /**
     * @return array
     */
    public function getNumericFields(): array
    {
        return $this->numericFields;
    }
}