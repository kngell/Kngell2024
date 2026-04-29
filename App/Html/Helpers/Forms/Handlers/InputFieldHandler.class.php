<?php

declare(strict_types=1);

class InputFieldHandler implements FieldHandlerInterface
{
    private const array INPUT_TYPE = ['text', 'number', 'email', 'password', 'tel', 'url', 'checkbox', 'radio'];

    public function supports(string $fieldType): bool
    {
        return in_array($fieldType, self::INPUT_TYPE);
    }

    public function handle(array $field, FormBuilder $form, AbstractForm $formInstance): AbstractHtmlComponent
    {
        $fieldId = $field['id'] ?? $formInstance->getFieldId($field);

        $class = ['input-field__input'];
        if ($field['type'] === 'checkbox') {
            $class = ['input-field__checkbox-input'];
        }

        $inputField = $form->input($field['type'] ?? 'text')
            ->name($field['name'])
            ->class(...$class)
            ->id($field['id'] ?? $fieldId)
            ->placeholder(' ');

        if (isset($field['value'])) {
            $inputField->value($field['value']);
        }

        // Set attributes
        if (!empty($field['required'])) {
            $inputField->required($field['required']);
        }

        if (!empty($field['disabled'])) {
            $inputField->disabled($field['disabled']);
        }

        if (!empty($field['readonly'])) {
            $inputField->readonly($field['readonly']);
        }

        if (!empty($field['maxlength'])) {
            $inputField->attribute('maxlength', $field['maxlength']);
        }

        // Input mode for better mobile experience
        if ($field['type'] === 'number') {
            $inputField->attribute('inputmode', 'numeric');
        }

        if ($field['type'] === 'tel') {
            $inputField->attribute('inputmode', 'tel');
        }

        return $inputField;
    }
}