<?php

declare(strict_types=1);

class PaypalPaymentGateway implements PaymentGatewayInterface
{
    private const array EXPERIENCE_CONTEXT = [
        //   'shipping_preference' => 'NO_SHIPPING',
        'contact_preference' => 'NO_CONTACT_INFO',
        'landing_page' => 'LOGIN',
        'user_action' => 'PAY_NOW',
        'payment_method_preference' => 'UNRESTRICTED',
        'return_url' => HOST . '/payments/complete-order',
        'cancel_url' => HOST . '/paypal/product',
        'brand_name' => 'K\'nGELL Shopping',
    ];
    private float $totalItemPrice = 0;
    private float $taxTotal = 0;
    private float $shipping;
    private float $handling;
    private float $shippingDiscount;
    private float $discount;
    private string $referenceId;
    private string $description;

    public function __construct(private ApiClientInterface $client, float $shipping = 0, float $handling = 0, float $shippingDiscount = 0, float $discount = 0, string $referenceId = 'PUHF', string $description = 'Sporting Goods')
    {
        $this->shipping = $shipping;
        $this->handling = $handling;
        $this->shippingDiscount = $shippingDiscount;
        $this->discount = $discount;
        $this->referenceId = $referenceId;
        $this->description = $description;
    }

    public function isSuccess(): bool
    {
        return true;
    }

    public function pay(array $cartItems, string $currency): string
    {
        $orderData = $this->orderData($cartItems, $currency);
        $orderLinks = $this->client->post('/v2/checkout/orders', $orderData);
        foreach ($orderLinks['links'] as $link) {
            if ($link['rel'] === 'payer-action') {
                return $link['href'];
            }
        }
        return '';
    }

    public function refund(string $transactionId, float $amount): string
    {
        return '';
    }

    public function getTransactionDetails(string $transactionId): array
    {
        return [];
    }

    public function getResponse(): mixed
    {
        return '';
    }

    private function orderData(array $cartItems, string $currency): array
    {
        $items = $this->items($cartItems, $currency);
        $amount = $this->amount($currency);
        return [
            'intent' => 'CAPTURE',
            'purchase_units' => [
                [
                    'reference_id' => $this->referenceId,
                    'description' => $this->description,
                    'amount' => $amount,
                    'items' => $items,
                ],
            ],
            'payment_source' => [
                'paypal' => [
                    'experience_context' => self::EXPERIENCE_CONTEXT,
                ],
            ],
        ];
    }

    private function format(float $number) : string
    {
        return number_format($number, 2, '.', '');
    }

    private function amount(string $currency) : array
    {
        return [
            'currency_code' => $currency,
            'value' => $this->format(
                $this->totalItemPrice + $this->taxTotal + $this->shipping + $this->handling + $this->shippingDiscount + $this->discount
            ),
            'breakdown' => [
                'item_total' => [
                    'currency_code' => $currency,
                    'value' => $this->format($this->totalItemPrice),
                ],
                'shipping' => [
                    'currency_code' => $currency,
                    'value' => $this->format($this->shipping),
                ],
                'handling' => [
                    'currency_code' => $currency,
                    'value' => $this->format($this->handling),
                ],
                'tax_total' => [
                    'currency_code' => $currency,
                    'value' => $this->format($this->taxTotal),
                ],
                'shipping_discount' => [
                    'currency_code' => $currency,
                    'value' => $this->format($this->shippingDiscount),
                ],
                'discount' => [
                    'currency_code' => $currency,
                    'value' => $this->format($this->discount),
                ],
            ],
        ];
    }

    private function items(array $cartItems, string $currency): array
    {
        $items = [];
        $amount = 0;
        $tax = 0;
        /** @var Cart $cartItem */
        foreach ($cartItems as $cartItem) {
            $unitAmount = (float) $cartItem->getItemPrice();
            $quantity = (string) $cartItem->getItemQuantity();
            $itemTax = $unitAmount * ($_ENV['TAX_RATE'] ?? 0);

            $amount += $unitAmount * $cartItem->getItemQuantity();
            $tax += $itemTax * $cartItem->getItemQuantity();

            $item = [
                'name' => $cartItem->getItemName() . ' by ' . $cartItem->getBrandName(),
                'unit_amount' => [
                    'currency_code' => $currency,
                    'value' => $this->format($unitAmount),
                ],
                'quantity' => $quantity,
            ];

            if ($cartItem->getDescription()) {
                $item['description'] = $cartItem->getDescription();
            }
            if ($cartItem->getCategoryName()) {
                $item['category'] = $cartItem->getCategoryName();
            }
            if ($itemTax > 0) {
                $item['tax'] = [
                    'currency_code' => $currency,
                    'value' => $this->format($itemTax),
                ];
            }
            if ($cartItem->getItemSku()) {
                $item['sku'] = $cartItem->getItemSku();
            }

            $items[] = $item;
        }
        $this->totalItemPrice = $amount;
        $this->taxTotal = $tax;
        return $items;
    }

    // private function items(array $cartItems, string $currency) : array
    // {
    //     $items = [];
    //     $amount = 0;
    //     $tax = 0;
    //     /** @var Cart $cartItem */
    //     foreach ($cartItems as $cartItem) {
    //         $subAmount = $cartItem->getItemPrice() * $cartItem->getItemQuantity();
    //         $tax += $subAmount * $_ENV['TAX_RATE'];
    //         $amount += $subAmount;
    //         $items[] = [
    //             'name' => $cartItem->getItemName() . ' by ' . $cartItem->getBrandName(),
    //             'description' => $cartItem->getDescription() ?? '',
    //             'quantity' => $cartItem->getItemQuantity(),
    //             'unit_amount' => [
    //                 'currency_code' => $currency,
    //                 'value' => $this->format($subAmount),
    //             ],
    //             'category' => $cartItem->getC * ategoryName() ?? '',
    //             'tax' => [
    //                 'currency_code' => $currency,
    //                 'value' => $this->format($subAmount * $_ENV['TAX_RATE']),
    //             ],
    //             'sku' => $cartItem->getItemSku() ?? '',
    //         ];
    //     }
    //     $this->totalItemPrice = $amount;
    //     $this->taxTotal = $tax;
    //     return $items;
    // }
}