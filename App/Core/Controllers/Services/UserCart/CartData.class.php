<?php

declare(strict_types=1);

use Brick\Money\Money;

final readonly class CartData
{
    public function __construct(
        public array $items,
        public int $totalCount,
        public Money $totalPrice,
        private MoneyManager $moneyManager,
    ) {
    }

    public function getTotalPriceFormatted(?string $locale = null): string
    {
        return $this->moneyManager->format($this->totalPrice);
    }

    public function getTotalPriceAmount(): float
    {
        return (float) $this->moneyManager->getAmount($this->totalPrice);
    }

    public function getTotalPriceWithTax(): Money
    {
        // If prices already include tax, return as is
        // This assumes all items have consistent tax handling
        return $this->totalPrice;
    }

    public function getTotalPriceWithTaxFormatted(): string
    {
        return $this->moneyManager->format($this->getTotalPriceWithTax());
    }

    public function toArray(): array
    {
        return [
            'items' => array_map(
                fn ($item) => $item instanceof CartItem ? $item->toArray() : $item,
                $this->items,
            ),
            'totalCount' => $this->totalCount,
            'totalPrice' => $this->getTotalPriceAmount(),
            'totalPriceFormatted' => $this->getTotalPriceFormatted(),
            'currency' => $this->moneyManager->getCurrencyCode(),
        ];
    }
}