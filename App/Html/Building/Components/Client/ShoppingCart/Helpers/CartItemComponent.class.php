<?php

declare(strict_types=1);

class CartItemComponent implements StandAloneComponentInterface
{
    private int $itemCounter = 0;

    public function __construct(
        private readonly HtmlBuilder $htmlBuilder,
        private readonly FieldIdGenerator $idGenerator,
        private ButtonBuilder $buttonBuilder,
        private readonly string $cartOperationBaseUrl = '/user-cart-operation', // Configurable
    ) {
    }

    public function build(mixed $cartItems = null): null|array|AbstractHtmlComponent
    {
        if (empty($cartItems)) {
            return null;
        }

        $html = $this->htmlBuilder;
        $cartItemComponents = [];

        foreach ($cartItems as $cartItem) {
            $cartItemComponents[] = $this->buildCartItem($cartItem);
        }

        return $html->div()->class('shopping-cart__items')->add(
            ...$cartItemComponents,
        );
    }

    private function buildCartItem(CartItem $cartItem): AbstractHtmlComponent
    {
        $html = $this->htmlBuilder;

        return $html->div()
            ->class('cart-item')
            ->custom(['data-product-id' => $cartItem->getItemId()])
            ->add(
                $this->buildImage($cartItem),
                $this->buildContent($cartItem),
            );
    }

    private function buildImage(CartItem $cartItem): ?AbstractHtmlComponent
    {
        $image = $cartItem->getImageUrl();
        if ($image === null) {
            return null;
        }

        $html = $this->htmlBuilder;
        return $html->div()->class('img-container')->add(
            $html->tag('img')
                ->class('image')
                ->src($cartItem->getImageUrl())
                ->alt($cartItem->getImageAlt() ?? 'product Image')
                ->loading('lazy'),
        );
    }

    private function buildContent(CartItem $cartItem): AbstractHtmlComponent
    {
        $html = $this->htmlBuilder;

        $contentInfo = $html->div()->class('content__info')->add(
            $html->tag('h5')->class('content__info--name')->content($cartItem->getName() ?? 'Product Name'),
            $html->tag('p')->class('content__info--category')->content($cartItem->getCategory() ?? 'Category'),
        );

        return $html->div()->class('content')->add(
            $contentInfo,
            $this->buildCartControls($cartItem),
        );
    }

    private function buildCartControls(CartItem $cartItem): AbstractHtmlComponent
    {
        $html = $this->htmlBuilder;
        $productId = (string) $cartItem->getItemId();
        $quantity = $cartItem->getQuantity();

        return $html->div()->class('content__count')->add(
            $this->buildActionButton($productId, $quantity, 'minus', '/update-down'),
            $this->buildQuantityDisplay($quantity, $productId),
            $this->buildActionButton($productId, $quantity, 'plus', '/update-up'),
            $this->buildPriceDisplay($cartItem),
            $this->buildActionButton($productId, $quantity, 'cancel', '/remove-item'),
        );
    }

    private function buildActionButton(string $productId, int $quantity, string $action, string $endpoint): AbstractHtmlComponent
    {
        $id = $this->idGenerator->getUniqueId('handle-item-' . $action);
        $actionUrl = $this->cartOperationBaseUrl . $endpoint;

        $hiddenFields = [
            $this->htmlBuilder->input('hidden')->name('product_id')->value($productId),
        ];

        if ($action !== 'cancel') {
            $hiddenFields[] = $this->htmlBuilder->input('hidden')->name('quantity')->value($quantity);
        }

        return $this->buttonBuilder->iconOnly(
            new IconConfig(
                icon: 'icon-' . $action,
                ariaLabel: $action,
            ),
        )->add(
            type: 'button',
            buttonClass: ['icon-container'],
            attributes: [
                'data-action' => $action,
                'aria-label' => ucfirst($action) . ' quantity',
            ],
        )->withForm(
            action: $actionUrl,
            method: 'post',
            includeCsrf: true,
            id: $id,
            classes: ['handle-item', 'handle-item-' . $action],
            externalComp: $hiddenFields,
        )->build();
    }

    private function buildQuantityDisplay(int $quantity, string $productId): AbstractHtmlComponent
    {
        $html = $this->htmlBuilder;

        return $html->div()->class('quantity-box')->add(
            $html->input('number')
                ->name('quantity')
                ->id($this->idGenerator->getUniqueId('quantity-box-input-id'))
                ->value($quantity)
                ->min(1)
                ->max(999)
                ->custom(['data-product-id' => $productId]),
        );
    }

    private function buildPriceDisplay(CartItem $cartItem): AbstractHtmlComponent
    {
        $html = $this->htmlBuilder;
        $price = $cartItem->getPrice();

        // Format price consistently
        $formattedPrice = is_numeric($price) ? number_format((float) $price, 2, ',', '.') : $price;

        return $html->tag('h5')
            ->class('content__count--price')
            ->content($formattedPrice . ' €');
    }
}