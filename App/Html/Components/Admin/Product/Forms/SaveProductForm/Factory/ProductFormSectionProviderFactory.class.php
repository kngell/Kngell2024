<?php

declare(strict_types=1);

class ProductFormSectionProviderFactory implements SectionProviderFactoryInterface
{
    public function __construct(
        private IconBuilder $iconBuilder,
        private CurrencyService $currencyService,
        private TaxClassService $taxClassService,
        private StockStatusService $stockStatusService,
        private CategoryService $CategoryService,
        private ProductStatusOptionsService $productStatusOptionsService,
        private BrandOptionsService $brandOptionsService,
    ) {
    }

    public function create(): SaveProductFormSectionProvider
    {
        return new SaveProductFormSectionProvider(
            $this->currencyService,
            $this->taxClassService,
            $this->stockStatusService,
            $this->CategoryService,
            $this->productStatusOptionsService,
            $this->brandOptionsService,
            $this->iconBuilder,
        );
    }
}
