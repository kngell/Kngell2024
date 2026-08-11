<?php

declare(strict_types=1);

class TextareaHandler implements FieldHandlerInterface
{
    public function supports(string $fieldType): bool
    {
        return $fieldType === 'textarea';
    }

    public function handle(array $field, FormBuilder $form, ?AbstractForm $formInstance = null, null|FormConfig|PageConfig $config = null): AbstractHtmlComponent
    {
        $fieldId = $config->getFieldId($field);
        return $form->textarea()
            ->name($field['name'])
            ->id($fieldId)
            ->placeholder($field['placeholder'] ?? '')
            ->class(InputBox::TEXTAREA_CLASS);
    }

    /**
     * @return null|string
     */
    public function getFieldId(): ?string
    {
        return null;
    }
}