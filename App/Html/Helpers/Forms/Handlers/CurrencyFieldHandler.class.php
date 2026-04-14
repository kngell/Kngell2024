<?php

declare(strict_types=1);

class CurrencyFieldHandler implements FieldHandlerInterface
{
    public function supports(string $fieldType): bool
    {
        return $fieldType === 'currency'; // Keep original type
    }

    public function handle(array $field, FormBuilder $form, AbstractForm $formInstance): AbstractHtmlComponent
    {
        $fieldId = $formInstance->getFieldId($field);
        return $this->createCurrencyBasic($field, $form, $formInstance, $fieldId);
    }

    private function createCurrencyBasic(array $field, FormBuilder $form, AbstractForm $formInstance, string $fieldId): AbstractHtmlComponent
    {
        $container = $form->tag('div')->class(InputBox::INPUT_CONTAINER_CURRENCY);

        // Currency select
        $currencySelect = $form->select()
            ->class(InputBox::INPUT_SELECT)
            ->name($field['currencyName'] ?? 'currency')
            ->add(...$this->buildCurrencyOptions($field, $form, $formInstance));

        $prefix = $form->tag('span')
            ->class(InputBox::PREFIX_CURRENCY_CLASS)
            ->add($currencySelect);

        $container->add($prefix);

        // Amount input
        $input = $form->input('number')
            ->name($field['name'])
            ->id($fieldId)
            ->placeholder($field['placeholder'] ?? '0.00')
            ->step((int) ($field['step'] ?? '0.01'))
            ->class(InputBox::INPUT_CLASS);

        $container->add($input);

        return $container;
    }

    private function buildCurrencyOptions(array $field, FormBuilder $form, AbstractForm $formInstance): array
    {
        $formValues = $formInstance->getFormValues(); // Need to pass this
        $currentValue = $formValues[$field['currencyName'] ?? 'currency'] ?? null;
        $defaultValue = $field['defaultCurrency'] ?? null;

        $currencies = $field['options'] ?? [];
        $options = [];

        foreach ($currencies as $value => $label) {
            $isSelected = $this->shouldSelectCurrency($currentValue, $defaultValue, $value);
            $options[] = $form->option((string) $value, $label)
                ->selected($isSelected);
        }

        return $options;
    }

    private function shouldSelectCurrency($currentValue, $defaultValue, $optionValue): bool
    {
        // Priority 1: Current value
        if ($currentValue !== null && (string) $currentValue === (string) $optionValue) {
            return true;
        }

        // Priority 2: Default value
        if ($defaultValue !== null && (string) $defaultValue === (string) $optionValue) {
            return true;
        }

        // Priority 3: First option (fallback)
        return false; // Will be handled by selecting first
    }
}