<?php

declare(strict_types=1);

class SaveProductFormSectionProvider extends AbstractSectionProvider
{
    public function __construct(
        private CurrencyService $currencyService,
        private TaxClassService $taxClassService,
        private StockStatusService $stockStatusService,
        private CategoryService $categoryService,
        private ProductStatusOptionsService $productStatusService,
        private BrandOptionsService $brandService,
        IconBuilder $iconBuilder,
    ) {
        parent::__construct($iconBuilder);
    }

    public function registerSections(HtmlBuilder $html, ?HtmlSectionManagerInterface $manager = null): void
    {
        $sections = [
            'general-information' => new GeneralInformationSection($html, $this->iconBuilder),
            'media' => new MediaSection($html, $this->iconBuilder),
            'pricing' => new PricingSection(
                $html,
                $this->iconBuilder,
                $this->currencyService,
                $this->taxClassService,
            ),
            'inventory' => new InventorySection(
                $html,
                $this->iconBuilder,
                $this->stockStatusService,
            ),
            'variation' => new VariationSection($html, $this->iconBuilder),
            'shipping' => new ShippingSection($html, $this->iconBuilder),
            'brand' => new BrandSection($html, $this->iconBuilder, $this->brandService),
            'category' => new CategorySection($html, $this->iconBuilder, $this->categoryService),
            'product-status' => new ProductStatusSection($html, $this->iconBuilder, $this->productStatusService),
        ];

        $registeredKeys = [];
        $this->register($manager, $sections, $registeredKeys);
        // Debug: log successfully registered sections
        error_log('Successfully registered sections: ' . implode(', ', $registeredKeys));
    }
}
