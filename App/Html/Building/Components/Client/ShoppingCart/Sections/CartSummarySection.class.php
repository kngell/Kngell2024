<?php

declare(strict_types=1);

class CartSummarySection extends AbstractBaseHtmlSection
{
    public function __construct(
        HtmlBuilder $htmlBuilder,
        IconBuilder $iconBuilder,
        private readonly ButtonBuilder $buttonBuilder,
        private readonly MoneyManager $moneyManager,
        private readonly TaxManager $taxManager,
    ) {
        parent::__construct($htmlBuilder, $iconBuilder);
    }

    #[Override]
    public function getConfig(array $cartItems = []): array|AbstractHtmlComponent
    {
        if (empty($cartItems)) {
            return [];
        }
        $cartSummary = new CartSummaryComponent(
            $this->htmlBuilder,
            $this->buttonBuilder,
            $this->iconBuilder,
            $this->moneyManager,
            $this->taxManager,
        );

        return $cartSummary->build($cartItems);
    }

    #[Override]
    public function getKey(): string
    {
        return ShoppingCartKeys::SUMMARY->value;
    }
}