<?php

declare(strict_types=1);
interface FormFieldMappingPayloadInterface
{
    public function isEmpty(): bool;

    public function getFieldMapping(): array;

    public function getNumericFields(): array;
}