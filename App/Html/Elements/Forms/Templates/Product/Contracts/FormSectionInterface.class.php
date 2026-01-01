<?php

declare(strict_types=1);

interface FormSectionInterface
{
    public function getKey(): string;

    public function getConfig(array $formValues = []): array;

    public function shouldRender(array $formValues = []): bool;

    public function getFieldMapping(): array;
}