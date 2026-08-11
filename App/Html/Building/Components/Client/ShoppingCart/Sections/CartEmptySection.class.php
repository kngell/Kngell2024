<?php

declare(strict_types=1);

class CartEmptySection extends AbstractBaseHtmlSection
{
    public function __construct(
        HtmlBuilder $htmlBuilder,
        IconBuilder $iconBuilder,
        private CartEmptyComponent $cartEmpty,
    ) {
        parent::__construct($htmlBuilder, $iconBuilder);
    }

    #[Override]
    public function getConfig(array $cartItems = []): array|AbstractHtmlComponent
    {
        if (empty($cartItems)) {
            return $this->cartEmpty->build();
        }
        return [];
    }

    #[Override]
    public function getKey(): string
    {
        return ShoppingCartKeys::EMPTY->value;
    }
}