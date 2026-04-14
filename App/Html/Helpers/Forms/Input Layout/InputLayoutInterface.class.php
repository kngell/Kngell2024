<?php

declare(strict_types=1);

interface InputLayoutInterface
{
    public function renderInput(
        array $field,
        AbstractHtmlComponent $inputElement,
        string $fieldId,
        FormBuilder $form,
        ?AbstractForm $formInstance = null,
    ): AbstractHtmlComponent;
}