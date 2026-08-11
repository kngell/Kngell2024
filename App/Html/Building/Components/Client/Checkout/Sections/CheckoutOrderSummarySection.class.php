<?php

declare(strict_types=1);

class CheckoutOrderSummarySection extends AbstractBaseHtmlSection
{
    public function __construct(
        HtmlBuilder $htmlBuilder,
        IconBuilder $iconBuilder,
        private readonly buttonBuilder $buttonBuilder,
        private readonly MoneyManager $moneyManager,
        private readonly TaxManager $taxManager,
    ) {
        parent::__construct($htmlBuilder, $iconBuilder);
    }

    public function getConfig(array $cartItems = []): array|AbstractHtmlComponent
    {
        $summary = new CartSummaryComponent(
            htmlBuilder: $this->htmlBuilder,
            buttonBuilder: $this->buttonBuilder,
            iconBuilder:$this->iconBuilder,
            moneyManager: $this->moneyManager,
            taxManager: $this->taxManager,
            showAction: true,
            isCheckoutProcess: true,
        );
        return $summary->build($cartItems);
    }

    public function getKey(): string
    {
        return CheckoutSection::SUMMARY->value;
    }
}