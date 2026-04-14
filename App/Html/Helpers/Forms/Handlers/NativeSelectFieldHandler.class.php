<?php

declare(strict_types=1);

class NativeSelectFieldHandler extends AbstractSelectHandler
{
    public function supports(string $fieldType): bool
    {
        return $fieldType === 'select';
    }

    protected function buildSelectElement(array $field, FormBuilder $form, AbstractForm $formInstance): AbstractHtmlComponent
    {
        $fieldId = $formInstance->getFieldId($field);

        $select = $form->select()
            ->id($fieldId)
            ->class('input-field__select')
            ->name($field['name'])
            ->placeholder(' ');

        // Set required attribute
        if (!empty($field['required'])) {
            $select->required();
        }

        // Set disabled attribute
        if (!empty($field['disabled'])) {
            $select->disabled($field['disabled']);
        }

        // Get current value
        $currentValue = $this->getSelectedValue($field, $formInstance);

        // Build options
        $options = $this->getOptionData($field, $formInstance);

        foreach ($options as $optionValue => $label) {
            $isSelected = $this->shouldSelectOption($currentValue, $optionValue, $field);
            $isDisabled = $this->isOptionDisabled($optionValue, $field);

            $option = $form->option((string) $optionValue, $label)
                ->selected($isSelected)
                ->disabled($isDisabled);

            if (!empty($field['optionClass']) and is_array($field['optionClass'])) {
                $option->class(...$field['optionClass']);
            }

            $select->add($option);
        }

        return $select;
    }

    protected function getOptionData(array $field, AbstractForm $formInstance): array
    {
        $options = parent::getOptionData($field, $formInstance);

        // Add placeholder as first option if provided
        if (!empty($field['placeholder']) && !isset($options[''])) {
            $options = ['' => $field['placeholder']] + $options;
        }

        return $options;
    }

    protected function isOptionDisabled($optionValue, array $field): bool
    {
        // Disable placeholder option if placeholder is set
        return $optionValue === '' && isset($field['placeholder']);
    }

    protected function getSelectedValue(array $field, AbstractForm $formInstance): ?string
    {
        // Get value from form values or direct field value
        $formValues = $formInstance->getFormValues();
        $value = $formValues[$field['name']] ?? $field['value'] ?? null;

        if (empty($value) && isset($field['placeholder'])) {
            return '';
        }

        return $value;
    }
}