<?php

declare(strict_types=1);

class TextareaFieldHandler implements FieldHandlerInterface
{
    public function supports(string $fieldType): bool
    {
        return $fieldType === 'textarea';
    }

    public function handle(array $field, FormBuilder $form, AbstractForm $formInstance): AbstractHtmlComponent
    {
        $fieldId = $formInstance->getFieldId($field);

        $textarea = $form->textarea()
            ->name($field['name'])
            ->id($fieldId)
            ->class('input-field__textarea')
            ->placeholder(' '); // Space for floating label

        // Set value if exists
        if (isset($field['value'])) {
            $textarea->content($field['value']);
        }

        // Set required attribute
        if (!empty($field['required'])) {
            $textarea->required($field['required']);
        }

        // Set disabled attribute
        if (!empty($field['disabled'])) {
            $textarea->disabled($field['disabled']);
        }

        // Set readonly attribute
        if (!empty($field['readonly'])) {
            $textarea->attribute('readonly', 'readonly');
        }

        // Set maxlength for counter
        if (!empty($field['maxlength'])) {
            $textarea->attribute('maxlength', $field['maxlength']);
        }

        // Set rows attribute
        if (!empty($field['rows'])) {
            $textarea->attribute('rows', $field['rows']);
        }

        return $textarea;
    }
}