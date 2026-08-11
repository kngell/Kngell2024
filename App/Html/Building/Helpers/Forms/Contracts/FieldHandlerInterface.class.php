<?php

declare(strict_types=1);

interface FieldHandlerInterface
{
    public function supports(string $fieldType): bool;

    public function handle(array $field, FormBuilder $form, ?AbstractForm $formInstance = null, null|FormConfig|PageConfig $config = null): AbstractHtmlComponent;

    public function getFieldId(): ?string;
}