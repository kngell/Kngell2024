<?php

declare(strict_types=1);

final class CartCollection
{
    private array $items = [];
    private ?int $cartPk = null;

    public function __construct(
        array $items,
        private readonly MoneyManager $moneyManager,
        private readonly TaxManager $taxManager,
        ?int $cartPk = null,
    ) {
        $this->cartPk = $cartPk;

        foreach ($items as $item) {
            if ($item instanceof CartItem) {
                $this->items[$item->getItemId()] = $item;
            } elseif (is_array($item) && isset($item['itemId'])) {
                $this->items[$item['itemId']] = CartItem::fromArray(
                    $item,
                    $this->moneyManager,
                    $this->taxManager,
                );
            }
        }
    }

    public function getCartPk(): ?int
    {
        return $this->cartPk;
    }

    public function setCartPk(int $cartPk): self
    {
        $this->cartPk = $cartPk;
        return $this;
    }

    public function hasCartPk(): bool
    {
        return $this->cartPk !== null;
    }

    public function addItem(int|string $itemId, int $quantity = 1, ?array $productData = null): void
    {
        if (isset($this->items[$itemId])) {
            $currentQuantity = $this->items[$itemId]->getQuantity();
            $this->items[$itemId]->setQuantity($currentQuantity + $quantity);
        } else {
            // Use provided product data or placeholders
            $name = $productData['name'] ?? 'Product ' . $itemId;
            $price = $productData['price'] ?? 0;
            $weight = $productData['weight'] ?? '';
            $imageUrl = $productData['imageUrl'] ?? null;
            $currencyCode = $productData['currency'] ?? $this->moneyManager->getCurrencyCode();
            $includesTax = $productData['includes_tax'] ?? $this->taxManager->shouldPriceIncludeTax();

            $this->items[$itemId] = new CartItem(
                itemId: $itemId,
                quantity: $quantity,
                weight: $weight,
                name: $name,
                price: $price,
                moneyManager: $this->moneyManager,
                taxManager: $this->taxManager,
                imageUrl: $imageUrl,
                currencyCode: $currencyCode,
                includesTax: $includesTax,
            );
        }
    }

    public function addItemWithData(int|string $productId, int $quantity, array $productData): void
    {
        $this->addItem($productId, $quantity, $productData);
    }

    public function removeItem(int $productId): void
    {
        unset($this->items[$productId]);
    }

    public function updateQuantity(int $productId, int $quantity): void
    {
        if ($quantity <= 0) {
            $this->removeItem($productId);
            return;
        }

        if (isset($this->items[$productId])) {
            $this->items[$productId]->setQuantity($quantity);
        }
    }

    public function getItems(): array
    {
        return array_values($this->items);
    }

    public function getItem(int $itemId): ?CartItem
    {
        return $this->items[$itemId] ?? null;
    }

    public function hasItem(int $productId): bool
    {
        return isset($this->items[$productId]);
    }

    public function getTotalCount(): int
    {
        $count = 0;
        foreach ($this->items as $item) {
            $count += $item->getQuantity();
        }
        return $count;
    }

    public function isEmpty(): bool
    {
        return empty($this->items);
    }

    public function toArray(): array
    {
        return [
            'cartPk' => $this->cartPk,
            'items' => array_map(
                fn (CartItem $item) => $item->toArray(),
                $this->items,
            ),
        ];
    }

    public static function fromArray(
        array $data,
        MoneyManager $moneyManager,
        TaxManager $taxManager,
    ): self {
        return new self(
            items: $data['items'] ?? $data,
            moneyManager: $moneyManager,
            taxManager: $taxManager,
            cartPk: $data['cartPk'] ?? null,
        );
    }
}