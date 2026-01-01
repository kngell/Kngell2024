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
        return $form->textarea()
            ->name($field['name'])
            ->id($fieldId)
            ->placeholder($field['placeholder'] ?? '')
            ->class(AbstractForm::TEXTAREA_CLASS);
    }
}
