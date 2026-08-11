<?php

declare(strict_types=1);

class InputBoxHandler extends AbstractBaseFieldHandler implements FieldHandlerInterface
{
    private const array INPUT_TYPE = ['text', 'number', 'email', 'password', 'tel', 'url', 'checkbox', 'radio'];

    public function supports(string $fieldType): bool
    {
        return in_array($fieldType, self::INPUT_TYPE);
    }

    public function handle(array $field, FormBuilder $form, ?AbstractForm $formInstance = null, null|FormConfig|PageConfig $config = null): AbstractHtmlComponent
    {
        $fieldId = $config->getFieldId($field);
        $inputfield = $form->input($field['type'] ?? 'text')
            ->name($field['name'])
            ->id($fieldId)
            ->placeholder($field['placeholder'] ?? '');

        $class = match ($field['type']) {
            'checkbox' => ['input-box__checkbox'],
            'radio ' => ['input-box__radio'],
            default => ['input-box__input']
        };
        return $inputfield->class(...$class);
    }
}