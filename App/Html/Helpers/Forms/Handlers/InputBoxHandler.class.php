<?php

declare(strict_types=1);

class InputBoxHandler implements FieldHandlerInterface
{
    private const array INPUT_TYPE = ['text', 'number', 'email', 'password', 'tel', 'url', 'checkbox', 'radio'];

    public function supports(string $fieldType): bool
    {
        return in_array($fieldType, self::INPUT_TYPE);
    }

    public function handle(array $field, FormBuilder $form, AbstractForm $formInstance): AbstractHtmlComponent
    {
        $fieldId = $formInstance->getFieldId($field);
        return $form->input($field['type'] ?? 'text')
            ->name($field['name'])
            ->id($fieldId)
            ->placeholder($field['placeholder'] ?? '')
            ->class(InputBox::INPUT_CLASS);
    }
}