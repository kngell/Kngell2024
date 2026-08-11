<?php

declare(strict_types=1);

class NativeSelectFieldHandler extends AbstractSelectHandler
{
    #[Override]
    protected function getSelectClasses(array $field): string
    {
        return 'input-field__select';
    }

    #[Override]
    protected function getOptionData(array $field, AbstractForm $formInstance): array
    {
        $options = parent::getOptionData($field, $formInstance);

        if (!empty($field['placeholder']) && !array_key_exists('', $options)) {
            $options = ['' => $field['placeholder']] + $options;
        }

        return $options;
    }

    #[Override]
    protected function isOptionDisabled(string|int $optionValue, array $field): bool
    {
        return $optionValue === '' && isset($field['placeholder']);
    }

    #[Override]
    protected function getSelectedValue(array $field, AbstractForm $formInstance): ?string
    {
        $value = parent::getSelectedValue($field, $formInstance);

        if (($value === null || $value === '') && isset($field['placeholder'])) {
            return '';
        }

        return $value;
    }

    #[Override]
    protected function configureOption(
        AbstractHtmlComponent $option,
        string|int $optionValue,
        mixed $label,
        array $field,
        FormBuilder $form,
        AbstractForm $formInstance,
    ): void {
        if (!empty($field['optionClass']) && is_array($field['optionClass'])) {
            $option->class(...$field['optionClass']);
        }
    }
}