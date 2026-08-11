<?php

declare(strict_types=1);

class CartListSection extends AbstractBaseHtmlSection
{
    public function __construct(
        HtmlBuilder $htmlBuilder,
        IconBuilder $iconBuilder,
        private CartItemComponent $cartItemComponent,
    ) {
        parent::__construct($htmlBuilder, $iconBuilder);
    }

    #[Override]
    public function getConfig(array $cartItems = []): array|AbstractHtmlComponent
    {
        if (empty($cartItems)) {
            return [];
        }
        return $this->cartItemComponent->build($cartItems);
    }

    #[Override]
    public function getKey(): string
    {
        return ShoppingCartKeys::LIST->value;
    }
}