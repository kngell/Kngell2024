<?php

declare(strict_types=1);

abstract class AbstractSelectHandler implements FieldHandlerInterface
{
    public function supports(string $fieldType): bool
    {
        return $fieldType === 'select';
    }

    public function handle(array $field, FormBuilder $form, AbstractForm $formInstance): AbstractHtmlComponent
    {
        return $this->buildSelectElement($field, $form, $formInstance);
    }

    protected function buildSelectElement(array $field, FormBuilder $form, AbstractForm $formInstance): AbstractHtmlComponent
    {
        $formValues = $formInstance->getFormValues();
        $fieldId = $formInstance->getFieldId($field);

        $select = $form->select()
            ->id($fieldId)
            ->class('input-field__select')
            ->name($field['name'])
            ->placeholder(' '); // Space for floating label

        // Set required attribute
        if (!empty($field['required'])) {
            $select->required();
        }

        // Set disabled attribute
        if (!empty($field['disabled'])) {
            $select->attribute('disabled', 'disabled');
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

            $select->add($option);
        }

        return $select;
    }

    protected function getOptionData(array $field, AbstractForm $formInstance): array
    {
        return $field['options'] ?? [];
    }

    protected function shouldSelectOption($currentValue, $optionValue, array $field): bool
    {
        // If current value matches option value
        if ($currentValue !== null && $currentValue !== '' && (string) $currentValue === (string) $optionValue) {
            return true;
        }

        // If no value and option is empty (placeholder)
        if (empty($currentValue) && $optionValue === '') {
            return true;
        }

        return false;
    }

    protected function isOptionDisabled($optionValue, array $field): bool
    {
        return false;
    }

    protected function getSelectedValue(array $field, AbstractForm $formInstance): ?string
    {
        $formValues = $formInstance->getFormValues();

        // Priority: form values > field value > null
        if (isset($formValues[$field['name']])) {
            return $formValues[$field['name']];
        }

        if (isset($field['value'])) {
            return $field['value'];
        }

        return null;
    }
}