<?php

declare(strict_types=1);

final class PricingSection extends BaseFormSection
{
    public function __construct(
        HtmlBuilder $builder,
        private CurrencyService $currencyService,
        private ProductRegionalPriceModel $productRegionalPriceModel,
        private TaxClassService $taxClassService,
    ) {
        parent::__construct($builder);
    }

    public function getKey(): string
    {
        return 'pricing';
    }

    public function getConfig(array $formValues = []): array
    {
        $currencies = $this->currencyService->getActiveCurrencies();
        $defaultCurrencyId = $this->currencyService->getDefaultCurrencyId();
        $taxClassOptions = $this->taxClassService->getActiveTaxClassOptions();
        return [
            [
                'key' => 'base-price',
                'name' => 'price',
                'label' => 'Base Price',
                'type' => 'currency', // Use currency type
                'placeholder' => '0.00',
                'step' => '0.01',
                'class' => 'span-all',
                'currencyName' => 'base_currency_id',
                'defaultCurrency' => $defaultCurrencyId ?? '',
                'options' => $currencies ?? '',
            ],
            [
                'key' => 'compare-price',
                'name' => 'compare_price',
                'label' => 'Compare Price',
                'type' => 'currency', // Use currency type
                'placeholder' => '0.00',
                'class' => 'span-all',
                'step' => '0.01',
                'defaultCurrency' => $defaultCurrencyId ?? '',
                'options' => $currencies ?? '',
            ],
            [
                'key' => 'cost-price',
                'name' => 'cost_price',
                'label' => 'Cost Price',
                'type' => 'currency', // Use currency type
                'placeholder' => '0.00',
                'step' => '0.01',
                'defaultCurrency' => $defaultCurrencyId ?? '',
                'options' => $currencies ?? '',
            ],
            [
                'key' => 'tax-class',
                'name' => 'tax_class_id',
                'label' => 'VAT class',
                'type' => 'select',
                'default' => $formValues['tax_class'] ?? '',
                'options' => $taxClassOptions,
                'suffixIcon' => 'icon-arrow-down',
            ],
            [
                'key' => 'price-includes-tax',
                'name' => 'price_includes_tax',
                'label' => 'Price includes tax',
                'class' => 'span-all',
                'type' => 'checkbox',
            ],
        ];
    }
}