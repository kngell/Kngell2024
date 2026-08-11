<?php

declare(strict_types=1);

abstract class AbstractSelectHandler extends AbstractSelectLikeHandler
{
    public function supports(string $fieldType): bool
    {
        return $fieldType === 'select';
    }

    public function handle(array $field, FormBuilder $form, ?AbstractForm $formInstance = null, null|FormConfig|PageConfig $config = null): AbstractHtmlComponent
    {
        return $this->buildSelectElement($field, $form, $formInstance, $config);
    }

    protected function buildSelectElement(
        array $field,
        FormBuilder $form,
        AbstractForm $formInstance,
        FormConfig|PageConfig $config,
    ): AbstractHtmlComponent {
        $fieldId = isset($field['id']) ? $field['id'] : $config->getFieldId($field);

        $select = $form->select()
            ->id($fieldId)
            ->class($this->getSelectClasses($field))
            ->name($field['name'])
            ->placeholder(' ');

        if (isset($field['attributes'])) {
            $select->custom($field['attributes']);
        }

        if (!empty($field['required'])) {
            $select->required();
        }

        if (!empty($field['disabled'])) {
            $select->disabled(true);
        }

        $this->configureSelect($select, $field, $form, $formInstance);

        $currentValue = $this->getSelectedValue($field, $formInstance);
        $options = $this->getOptionData($field, $formInstance);

        foreach ($options as $optionValue => $label) {
            if (isset($field['label']) && $optionValue === '') {
                continue;
            }
            $option = $form->option((string) $optionValue, (string) $label)
                ->selected($this->shouldSelectOption($currentValue, $optionValue, $field))
                ->disabled($this->isOptionDisabled($optionValue, $field));

            $this->configureOption($option, $optionValue, $label, $field, $form, $formInstance);
            $select->add($option);
        }

        return $this->decorateSelect($select, $field, $form, $formInstance);
    }

    protected function shouldSelectOption(
        null|string|array $currentValue,
        string|int $optionValue,
        array $field,
    ): bool {
        $isMultiSelect = $this->isMultiSelectField($field);

        if ($isMultiSelect) {
            return $this->shouldSelectMultiOption($currentValue, $optionValue);
        }

        return $this->shouldSelectSingleOption($currentValue, $optionValue);
    }

    protected function shouldSelectMultiOption(
        null|string|array $currentValue,
        string|int $optionValue,
    ): bool {
        // Convert to array if needed
        $selectedValues = is_array($currentValue) ? $currentValue : [];

        // Convert all to strings for comparison
        $selectedValues = array_map('strval', $selectedValues);
        $optionValueStr = (string) $optionValue;

        // Empty value handling
        if ($optionValueStr === '') {
            // Don't auto-select empty option in multi-select
            return false;
        }

        return in_array($optionValueStr, $selectedValues, true);
    }

    protected function shouldSelectSingleOption(
        null|string|array $currentValue,
        string|int $optionValue,
    ): bool {
        // Ensure current value is a string for single select
        $currentValue = is_array($currentValue) ? null : $currentValue;

        // Handle empty placeholder option
        if (($currentValue === null || $currentValue === '') && $optionValue === '') {
            return true;
        }

        // Handle normal value comparison
        if ($currentValue !== null && $currentValue !== '') {
            return (string) $currentValue === (string) $optionValue;
        }

        return false;
    }

    protected function isMultiSelectField(array $field): bool
    {
        // Check for array syntax in name
        if (isset($field['name']) && str_ends_with($field['name'], '[]')) {
            return true;
        }

        // Check for multiple attribute
        if (isset($field['multiple']) && $field['multiple'] === true) {
            return true;
        }

        if (isset($field['attributes']['multiple'])) {
            return true;
        }

        return false;
    }

    // protected function shouldSelectOption(
    //     null|string|array $currentValue,
    //     string|int $optionValue,
    //     array $field,
    // ): bool {
    //     if (is_array($optionValue) || is_array($currentValue)) {
    //         $stop = true;
    //     }
    //     if (is_array($currentValue)) {
    //         if (in_array($optionValue, $currentValue)) {
    //             return true;
    //         } else {
    //             return false;
    //         }
    //     }
    //     if ($currentValue !== null && $currentValue !== '' && (string) $currentValue === (string) $optionValue) {
    //         return true;
    //     }

    //     if (($currentValue === null || $currentValue === '') && $optionValue === '') {
    //         return true;
    //     }

    //     return false;
    // }

    protected function isOptionDisabled(string|int $optionValue, array $field): bool
    {
        return false;
    }

    protected function configureSelect(
        AbstractHtmlComponent $select,
        array $field,
        FormBuilder $form,
        AbstractForm $formInstance,
    ): void {
    }

    protected function configureOption(
        AbstractHtmlComponent $option,
        string|int $optionValue,
        mixed $label,
        array $field,
        FormBuilder $form,
        AbstractForm $formInstance,
    ): void {
    }

    protected function decorateSelect(
        AbstractHtmlComponent $select,
        array $field,
        FormBuilder $form,
        AbstractForm $formInstance,
    ): AbstractHtmlComponent {
        return $select;
    }

    abstract protected function getSelectClasses(array $field): string;
}