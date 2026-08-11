<?php

declare(strict_types=1);

use Brick\Money\Money;

final class CartItem
{
    private Money $price;

    public function __construct(
        private int|string $itemId,
        private int $quantity,
        private string $weight,
        private string $name,
        int|float|string|Money $price,
        private readonly MoneyManager $moneyManager,
        private readonly TaxManager $taxManager,
        private ?string $imageUrl = null,
        private ?string $imageAlt = null,
        private ?string $category = null,
        private ?string $currencyCode = null,
        private ?bool $includesTax = null,
    ) {
        $this->includesTax = $includesTax ?? $this->taxManager->shouldPriceIncludeTax();

        if ($price instanceof Money) {
            $this->price = $price;
        } else {
            $this->price = $this->moneyManager->createMoney($price, $this->currencyCode);
        }
    }

    public function getItemId(): int
    {
        return $this->itemId;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): self
    {
        $this->quantity = $quantity;
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPrice(): ?string
    {
        return $this->price->getAmount()->toString();
    }

    public function getPriceFormatted(): string
    {
        return $this->moneyManager->format($this->price);
    }

    public function getPriceWithTax(): Money
    {
        if ($this->includesTax) {
            return $this->price;
        }

        return $this->taxManager->addTax($this->price);
    }

    public function getPriceWithTaxFormatted(): string
    {
        return $this->moneyManager->format($this->getPriceWithTax());
    }

    public function getPriceWithoutTax(): Money
    {
        if (!$this->includesTax) {
            return $this->price;
        }

        return $this->taxManager->removeTax($this->price);
    }

    public function getPriceWithoutTaxFormatted(): string
    {
        return $this->moneyManager->format($this->getPriceWithoutTax());
    }

    public function includesTax(): bool
    {
        return $this->includesTax;
    }

    public function getTotalPrice(): Money
    {
        $price = $this->getPriceWithTax();
        return $price->multipliedBy($this->quantity);
    }

    public function getTotalPriceFormatted(): string
    {
        return $this->moneyManager->format($this->getTotalPrice());
    }

    public function getImageUrl(): ?string
    {
        return $this->imageUrl;
    }

    public function toArray(): array
    {
        return [
            'itemId' => $this->itemId,
            'quantity' => $this->quantity,
            'weight' => $this->weight,
            'name' => $this->name,
            'price' => $this->moneyManager->getAmount($this->price),
            'priceFormatted' => $this->getPriceFormatted(),
            'priceWithTax' => $this->moneyManager->getAmount($this->getPriceWithTax()),
            'priceWithTaxFormatted' => $this->getPriceWithTaxFormatted(),
            'priceWithoutTax' => $this->moneyManager->getAmount($this->getPriceWithoutTax()),
            'priceWithoutTaxFormatted' => $this->getPriceWithoutTaxFormatted(),
            'totalPrice' => $this->moneyManager->getAmount($this->getTotalPrice()),
            'totalPriceFormatted' => $this->getTotalPriceFormatted(),
            'includesTax' => $this->includesTax,
            'taxRate' => $this->taxManager->getTaxRate(),
            'currency' => $this->price->getCurrency()->getCurrencyCode(),
            'imageUrl' => $this->imageUrl,
        ];
    }

    /**
     * @return null|string
     */
    public function getImageAlt(): ?string
    {
        return $this->imageAlt;
    }

    /**
     * @return null|string
     */
    public function getCategory(): ?string
    {
        return $this->category;
    }

    /**
     * @return string
     */
    public function getWeight(): string
    {
        return $this->weight;
    }

    public static function fromArray(
        array $data,
        MoneyManager $moneyManager,
        TaxManager $taxManager,
    ): self {
        return new self(
            itemId: $data['itemId'],
            quantity: $data['quantity'],
            weight: $data['weight'],
            name: $data['name'],
            price: $data['price'],
            moneyManager: $moneyManager,
            taxManager: $taxManager,
            imageUrl: $data['imageUrl'] ?? null,
            currencyCode: $data['currency'] ?? null,
            includesTax: $data['includesTax'] ?? null,
        );
    }
}