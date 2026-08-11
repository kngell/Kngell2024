<?php

declare(strict_types=1);

use Brick\Money\Money;

final class CartSummaryComponent implements StandAloneComponentInterface
{
    private const FREE_SHIPPING_THRESHOLD = 100.00;
    private const SHIPPING_COST = '29.00';

    public function __construct(
        private readonly HtmlBuilder $htmlBuilder,
        private readonly ButtonBuilder $buttonBuilder,
        private readonly IconBuilder $iconBuilder,
        private readonly MoneyManager $moneyManager,
        private readonly TaxManager $taxManager,
        private bool $showAction = true,
        private bool $isCheckoutProcess = false,
    ) {
        $this->showAction = $showAction;
    }

    /**
     * @param array<CartItem|CartResponse|array>|null $params
     */
    public function build(mixed $params = null): null|array|AbstractHtmlComponent
    {
        $cartItems = $this->extractCartItems($params);

        if (empty($cartItems)) {
            return null;
        }

        $subtotal = $this->getSubtotal($cartItems);
        $tax = $this->getTax($cartItems);
        $shipping = $this->getShipping($cartItems);
        $discount = $this->getDiscount($cartItems);
        $total = $subtotal->plus($tax)->plus($shipping)->minus($discount);

        return $this->htmlBuilder->div()
            ->class('shopping-cart__summary')
            ->add(
                $this->buildTitle(),
                $this->buildSummaryContent($subtotal, $tax, $shipping, $discount, $total),
            );
    }

    private function extractCartItems(mixed $cartData): array
    {
        if ($cartData instanceof CartData) {
            return $cartData->items ?? [];
        }

        if ($cartData instanceof CartResponse) {
            return [$cartData];
        }

        if (is_array($cartData)) {
            return $cartData;
        }

        return [];
    }

    // ─── Build Methods ───────────────────────────────────────────

    private function buildTitle(): AbstractHtmlComponent
    {
        return $this->htmlBuilder
            ->tag('h4')
            ->class('title')
            ->content('Order Summary');
    }

    private function buildSummaryContent(
        Money $subtotal,
        Money $tax,
        Money $shipping,
        Money $discount,
        Money $total,
    ): AbstractHtmlComponent {
        $html = $this->htmlBuilder;

        return $html->div()->class('shopping-cart__summary--content')->add(
            $this->buildSubtotalSection($subtotal, $tax, $shipping, $discount),
            $this->buildTotalSection($total),
            $this->buildActions(),
            $this->trust(),
        );
    }

    private function buildSubtotalSection(
        Money $subtotal,
        Money $tax,
        Money $shipping,
        Money $discount,
    ): AbstractHtmlComponent {
        $html = $this->htmlBuilder;

        return $html->div()->class('subtotal')->add(
            $this->buildCouponSection(),
            $this->buildPriceDetails($subtotal, $tax, $shipping, $discount),
        );
    }

    private function buildPriceDetails(
        Money $subtotal,
        Money $tax,
        Money $shipping,
        Money $discount,
    ): AbstractHtmlComponent {
        $html = $this->htmlBuilder;

        $subtotalItems = $html->div()->class('subtotal__price--items')->add(
            $html->tag('h6')->class('subtotal__price--items-title')->content('Subtotal'),
            $html->tag('span')->class('subtotal__price--items-value')->content($this->formatMoney($subtotal)),
        );

        $taxes = $html->div()->class('subtotal__price--taxes')->add(
            $this->buildTaxRow('Estimated Tax', $tax),
            $this->buildTaxRow('Estimated shipping & Handling', $shipping),
        );

        // Add discount row if discount > 0
        $discountAmount = (float) (string) $discount->getAmount();
        if ($discountAmount > 0) {
            $taxes->add(
                $this->buildTaxRow('Discount', $discount, true),
            );
        }

        return $html->div()->class('subtotal__price')->add(
            $subtotalItems,
            $taxes,
        );
    }

    private function buildTaxRow(string $label, Money $amount, bool $isDiscount = false): AbstractHtmlComponent
    {
        $html = $this->htmlBuilder;
        $valueClass = $isDiscount ? 'taxes-text__value discount' : 'taxes-text__value';

        return $html->div()->class('taxes-text')->add(
            $html->tag('p')->class('taxes-text__title')->content($label),
            $html->tag('p')->class($valueClass)->content($this->formatMoney($amount)),
        );
    }

    private function buildTotalSection(Money $total): AbstractHtmlComponent
    {
        $html = $this->htmlBuilder;

        return $html->div()->class('total-price')->add(
            $html->tag('h6')->class('title')->content('Total'),
            $html->tag('h6')->class('value')->content($this->formatMoney($total)),
        );
    }

    private function buildActions(): ?AbstractHtmlComponent
    {
        $html = $this->htmlBuilder;
        if (!$this->showAction) {
            return null;
        }
        if ($this->isCheckoutProcess) {
            return $this->buttonBuilder->addConfig(
                new ButtonConfig(
                    type: 'submit',
                    label: 'Place Order',
                    size: 'lg',
                    style: 'checkout-submit',
                    ariaLabel: 'Place Order',
                ),
            )->build();
        }
        return $html->div()->class('btns')->add(
            $html->tag('a')->href('/checkout')
                ->class('btn btn-dark')
                ->content('Proceed to Checkout'),
        );
    }

    private function trust(): AbstractHtmlComponent
    {
        $html = $this->htmlBuilder;
        return $html->div()->class('summary-trust')->add(
            $html->div()->class('summary-trust__item')->add(
                $this->iconBuilder->createIcon(
                    icon: 'icon-trust',
                    ariaLabel: 'Trust',
                    iconClass: ['trust'],
                ),
                $html->span()->content('Secure SSL checkout'),
            ),
            $html->div()->class('summary-trust__item')->add(
                $this->iconBuilder->createIcon(
                    icon: 'icon-delivery',
                    ariaLabel: 'Delivery',
                    iconClass: ['trust'],
                ),
                $html->span()->content('Fast delivery'),
            ),
        );
    }

    private function buildCouponSection(): AbstractHtmlComponent
    {
        $html = $this->htmlBuilder;

        $tabConfig = TabConfig::create()
            ->setTabLabelContainerClass(['subtotal__coupon-tabs-label'])
            ->setContentContainerClass(['tabs__content'])
            ->addTab(
                TabItem::create('discount-code', 'Discount Code')
                    ->setState('active')
                    ->setContentClass(['subtotal__coupon--code'])
                    ->setSectionGroups(['discount-group'])
                    ->setPriority(1),
            )
            ->addTab(
                TabItem::create('promo-code', 'Promo Code')
                    ->setContentClass(['subtotal__coupon--code'])
                    ->setSectionGroups(['promo-group'])
                    ->setPriority(2),
            );

        // Build SectionGroupManager
        $sectionGroupManager = SectionGroupManager::create()
            ->addGroup(
                SectionGroup::create('discount-group')
                    ->setSectionKeys(['discount-field']),
            )
            ->addGroup(
                SectionGroup::create('promo-group')
                    ->setSectionKeys(['promo-field']),
            );

        // Build section components
        $inputHelper = StandaloneInputHelper::create($this->htmlBuilder);
        $idGenerator = new FieldIdGenerator();

        $sectionComponents = [
            'discount-field' => $this->buildDiscountCodeInput($inputHelper, $idGenerator),
            'promo-field' => $this->buildPromoCodeInput($inputHelper, $idGenerator),
        ];

        // Build apply button
        $applyButton = $this->buttonBuilder->add(
            type: 'button',
            label: 'Apply',
            buttonClass: ['subtotal__coupon--apply'],
        )->build();

        $tabComponent = TabComponent::create(
            htmlBuilder:$html,
            tabConfig: $tabConfig,
            sectionGroupManager: $sectionGroupManager,
            config:TabComponentConfig::simpleTabs(),
        )
            ->setSectionComponents($sectionComponents)
            ->setContainerClass(['subtotal__coupon'])
            ->setContentContainerClass(['subtotal__coupon-content'])
            ->setPanelClass(['subtotal__coupon--panel'])
            ->returnAsArray(false)
            ->build();

        if ($tabComponent instanceof AbstractHtmlComponent) {
            $tabComponent->add($applyButton);
        }

        return $tabComponent;
    }

    private function buildDiscountCodeInput(
        StandaloneInputHelper $inputHelper,
        FieldIdGenerator $idGenerator,
    ): ?AbstractHtmlComponent {
        $field = FormFieldConfig::create(
            name: 'discount-code',
            type: 'text',
            label: 'Enter discount code...',
            placeholder: ' ',
            attributes: [
                'autocomplete' => 'off',
            ],
            id: $idGenerator->getUniqueId(),
        );

        return $inputHelper->build(['field' => $field]);
    }

    private function buildPromoCodeInput(
        StandaloneInputHelper $inputHelper,
        FieldIdGenerator $idGenerator,
    ): AbstractHtmlComponent {
        $html = $this->htmlBuilder;
        $field = FormFieldConfig::create(
            name: 'promo-code',
            type: 'text',
            label: 'Enter promo code...',
            placeholder: ' ',
            attributes: [
                'autocomplete' => 'off',
            ],
            id: $idGenerator->getUniqueId(),
        );

        return $inputHelper->build(['field' => $field]);
    }

    // ─── Data Extraction Methods ─────────────────────────────────

    /**
     * @param array<CartItem|CartResponse|array> $cartItems
     */
    private function getSubtotal(array $cartItems): Money
    {
        $total = $this->moneyManager->zero();

        foreach ($cartItems as $item) {
            if ($item instanceof CartResponse) {
                return $item->getTotalPrice();
            }

            if ($item instanceof CartItem) {
                $total = $total->plus($item->getTotalPrice());
            }

            if (is_array($item) && isset($item['total_price'])) {
                $total = $total->plus($this->moneyManager->createMoney((string) $item['total_price']));
            }
        }

        return $total;
    }

    /**
     * @param array<CartItem|CartResponse|array> $cartItems
     */
    private function getTax(array $cartItems): Money
    {
        $total = $this->moneyManager->zero();

        foreach ($cartItems as $item) {
            if ($item instanceof CartItem) {
                $priceWithoutTax = $item->getPriceWithoutTax();
                $taxAmount = $this->taxManager->calculateTax($priceWithoutTax);
                $total = $total->plus($taxAmount);
            }
        }

        return $total;
    }

    /**
     * @param array<CartItem|CartResponse|array> $cartItems
     */
    private function getShipping(array $cartItems): Money
    {
        $subtotal = $this->getSubtotal($cartItems);
        $subtotalAmount = (float) (string) $subtotal->getAmount();

        // Free shipping over threshold
        if ($subtotalAmount >= self::FREE_SHIPPING_THRESHOLD) {
            return $this->moneyManager->zero();
        }

        return $this->moneyManager->createMoney(self::SHIPPING_COST);
    }

    /**
     * @param array<CartItem|CartResponse|array> $cartItems
     */
    private function getDiscount(array $cartItems): Money
    {
        // TODO: Implement coupon/promo logic
        return $this->moneyManager->zero();
    }

    private function formatMoney(Money $money): string
    {
        return $this->moneyManager->format($money);
    }
}