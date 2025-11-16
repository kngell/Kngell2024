<?php

declare(strict_types=1);

class ProductSectionServiceProvider
{
    public function __construct(
        private CurrencyService $currencyService,
        private ProductRegionalPriceModel $productRegionalPriceModel,
        private TaxClassService $taxClassService,
        private StockStatusService $stockStatusService,
        private CategoryModel $categoryModel,
    ) {
    }

    public function registerSections(FormSectionManager $manager, HtmlBuilder $form): void
    {
        $sections = [
            'general-information' => new GeneralInformationSection($form),
            'media' => new MediaSection($form),
            'pricing' => new PricingSection(
                $form,
                $this->currencyService,
                $this->productRegionalPriceModel,
                $this->taxClassService,
            ),
            'inventory' => new InventorySection(
                $form,
                $this->stockStatusService,
            ),
            'variation' => new VariationSection($form),
            'shipping' => new ShippingSection($form),
            'category' => new CategorySection($form, $this->categoryModel),
        ];

        $registeredKeys = [];

        foreach ($sections as $expectedKey => $section) {
            $actualKey = $section->getKey();

            if ($actualKey !== $expectedKey) {
                throw new LogicException(
                    'Section key mismatch for ' . get_class($section) .
                    ": expected '{$expectedKey}', got '{$actualKey}'",
                );
            }

            $manager->registerSection($section);
            $registeredKeys[] = $actualKey;
        }

        // Debug: log successfully registered sections
        error_log('Successfully registered sections: ' . implode(', ', $registeredKeys));
    }
}