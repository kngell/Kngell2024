<?php

declare(strict_types=1);

class CheckoutPageConfigFactory extends AbstractRegularPageConfigFactory
{
    #[Override]
    public function getEnumClass(): string
    {
        return ShoppingCartKeys::class;
    }

    #[Override]
    public function getAssets(): array
    {
        return [
            'css' => 'css/frontend/ecommerce/pages/shopping-cart',
        ];
    }

    #[Override]
    public function getExpectedControllerClass(): ?string
    {
        return ShoppingCartController::class;
    }

    protected function buildSections(): array
    {
        return [
            CartListSection::class,
            CartSummarySection::class,
            CartEmptySection::class,
        ];
    }
}