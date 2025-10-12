<?php

declare(strict_types=1);

class InputFieldHandler implements FieldHandlerInterface
{
    public function supports(string $fieldType): bool
    {
        return in_array($fieldType, ['text', 'number', 'email', 'password', 'tel', 'url', 'checkbox', 'radio']);
    }

    public function handle(array $field, FormBuilder $form, AbstractForm $formInstance): AbstractHtmlComponent
    {
        $fieldId = $formInstance->getFieldId($field);
        return $form->input($field['type'] ?? 'text')
            ->name($field['name'])
            ->id($fieldId)
            ->placeholder($field['placeholder'] ?? '')
            ->class(AbstractForm::INPUT_CLASS);
    }
}