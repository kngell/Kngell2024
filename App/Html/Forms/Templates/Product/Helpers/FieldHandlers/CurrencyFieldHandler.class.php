<?php

declare(strict_types=1);

class CurrencyFieldHandler implements FieldHandlerInterface
{
    public function supports(string $fieldType): bool
    {
        return $fieldType === 'currency';
    }

    public function handle(array $field, FormBuilder $form, AbstractForm $formInstance): AbstractHtmlComponent
    {
        $fieldId = $formInstance->getFieldId($field);

        return $this->createCurrencyBasic($field, $form, $formInstance, $fieldId);
    }

    private function createCurrencyBasic(array $field, FormBuilder $form, AbstractForm $formInstance, string $fieldId): AbstractHtmlComponent
    {
        $container = $form->tag('div')->class(AbstractForm::INPUT_CONTAINER_CURRENCY);

        // Currency select as prefix
        $currencySelect = $form->select()
            ->class(AbstractForm::INPUT_SELECT)
            ->name($field['currencyName'] ?? 'currency')
            ->add(...$this->buildCurrencyOptions($field, $form));

        $prefix = $form->tag('span')
            ->class(AbstractForm::PREFIX_CURRENCY_CLASS)
            ->add($currencySelect);

        $container->add($prefix);

        // Amount input
        $input = $form->input('number')
            ->name($field['name'])
            ->id($fieldId)
            ->placeholder($field['placeholder'] ?? '0.00')
            ->step((int) ($field['step'] ?? '0.01'))
            ->class(AbstractForm::INPUT_CLASS);

        $container->add($input);

        return $container;
    }

    private function buildCurrencyOptions(array $field, FormBuilder $form): array
    {
        $currencies = $field['options'] ?? [
            '1' => 'USD',
            '2' => 'EUR',
            '3' => 'GBP',
            '4' => 'JPY',
            '5' => 'CAD',
        ];
        if ($currencies) {
            $options = [];
            foreach ($currencies as $value => $label) {
                $isSelected = ($value === ($field['defaultCurrency'] ?? ''));
                $options[] = $form->option((string) $value, $label)
                    ->selected($isSelected);
            }

            return $options;
        }
        return $currencies;
    }
}