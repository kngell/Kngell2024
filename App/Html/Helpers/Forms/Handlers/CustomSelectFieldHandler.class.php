<?php

declare(strict_types=1);

class CustomSelectFieldHandler implements FieldHandlerInterface
{
    public function supports(string $fieldType): bool
    {
        return in_array($fieldType, ['custom-select']);
    }

    public function handle(array $field, FormBuilder $form, AbstractForm $formInstance): AbstractHtmlComponent
    {
        $fieldId = $formInstance->getFieldId($field);
        $currentValue = $formInstance->getFormValues()[$field['name']] ?? $field['value'] ?? null;
        $currentLabel = $this->getCurrentLabel($currentValue, $field);
        $hasValue = !empty($currentValue);

        $button = $form->button()
            ->type('button')
            ->class(...$this->getButtonClasses($field, $hasValue))
            ->id($fieldId)
            ->attribute('data-field-name', $field['name'])
            ->attribute('data-has-value', $hasValue ? 'true' : 'false');

        // Add text span
        $textSpan = $form->tag('span')
            ->class('text', ($currentLabel ? '' : 'placeholder'))
            ->content($currentLabel ?: ($field['placeholder'] ?? 'Select option'));
        $button->add($textSpan);

        return $button;
    }

    protected function getButtonClasses(array $field, bool $hasValue): array
    {
        $classes = $field['class'] ?? [];
        $classes[] = 'input-field__custom-select';

        if ($hasValue) {
            $classes[] = 'has-value';
        }

        return $classes;
    }

    protected function getCurrentLabel($currentValue, array $field): ?string
    {
        if ($currentValue === null || $currentValue === '') {
            return null;
        }

        $options = $field['options'] ?? [];
        return $options[$currentValue] ?? null;
    }
}