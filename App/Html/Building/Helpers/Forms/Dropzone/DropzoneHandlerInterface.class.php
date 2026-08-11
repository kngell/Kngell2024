<?php

declare(strict_types=1);

interface DropzoneHandlerInterface
{
    public function getName(): string;

    public function renderEmpty(
        array $field,
        FormBuilder $form,
        AbstractForm $formInstance,
        string $fieldId,
    ): AbstractHtmlComponent;

    public function renderPopulated(
        array $field,
        FormBuilder $form,
        array $files,
        string $fieldId,
    ): AbstractHtmlComponent;

    // public function getClassConfig(): array;
}