<?php

declare(strict_types=1);

class CustomSelectFieldHandler extends AbstractSelectLikeHandler
{
    public function supports(string $fieldType): bool
    {
        return $fieldType === 'custom-select';
    }

    public function handle(array $field, FormBuilder $form, AbstractForm $formInstance): AbstractHtmlComponent
    {
        $fieldId = $formInstance->getFieldId($field);
        $currentValue = $this->getSelectedValue($field, $formInstance);
        $currentLabel = $this->getCurrentLabel($currentValue, $field, $formInstance);
        $hasValue = $this->hasValue($currentValue);

        $button = $form->button()
            ->type('button')
            ->class(...$this->getButtonClasses($field, $hasValue))
            ->id($fieldId)
            ->attribute('data-field-name', $field['name'])
            ->attribute('data-has-value', $hasValue ? 'true' : 'false');

        $textSpan = $form->tag('span')
            ->class('text', $currentLabel !== null ? '' : 'placeholder')
            ->content($currentLabel ?? ($field['placeholder'] ?? 'Select option'));

        $button->add($textSpan);

        return $button;
    }

    protected function getButtonClasses(array $field, bool $hasValue): array
    {
        $classes = is_array($field['class'] ?? null) ? $field['class'] : [];
        $classes[] = 'input-field__custom-select';

        if ($hasValue) {
            $classes[] = 'has-value';
        }

        return $classes;
    }
}