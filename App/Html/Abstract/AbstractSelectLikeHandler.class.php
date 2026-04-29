<?php

declare(strict_types=1);

abstract class AbstractSelectLikeHandler implements FieldHandlerInterface
{
    protected function getOptionData(array $field, AbstractForm $formInstance): array
    {
        return $field['options'] ?? [];
    }

    protected function getSelectedValue(array $field, AbstractForm $formInstance): ?string
    {
        $formValues = $formInstance->getFormValues();

        if (array_key_exists($field['name'], $formValues)) {
            $value = $formValues[$field['name']];
            return $value === null ? null : (string) $value;
        }

        if (array_key_exists('value', $field)) {
            $value = $field['value'];
            return $value === null ? null : (string) $value;
        }

        return null;
    }

    protected function getCurrentLabel(?string $currentValue, array $field, AbstractForm $formInstance): ?string
    {
        if ($currentValue === null || $currentValue === '') {
            return null;
        }

        $options = $this->getOptionData($field, $formInstance);

        return isset($options[$currentValue]) ? (string) $options[$currentValue] : null;
    }

    protected function hasValue(?string $value): bool
    {
        return $value !== null && $value !== '';
    }
}