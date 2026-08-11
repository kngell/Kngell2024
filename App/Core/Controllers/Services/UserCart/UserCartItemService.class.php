<?php

declare(strict_types=1);

use Brick\Money\Money;

final class UserCartItemService
{
    public function __construct(
        private readonly CartService $cartService,
        private readonly ObfuscatorManager $obfuscator,
        private readonly MoneyManager $moneyManager,
        private readonly TaxManager $taxManager,
    ) {
    }

    public function addItem(int|string $productId, int $quantity = 1, array $productData = []): CartData
    {
        if ($this->obfuscator->isObfuscated($productId)) {
            $productId = $this->obfuscator->deobfuscate($productId);
        }
        $result = $this->cartService->addItem($productId, $quantity, $productData);

        if (!$result->isSuccess()) {
            throw new RuntimeException($result->error ?? 'Failed to add item');
        }
        return $this->cartService->getCartData();
    }

    public function removeItem(int $productId): CartData
    {
        $result = $this->cartService->removeItem($productId);

        if (!$result->isSuccess()) {
            throw new RuntimeException($result->error ?? 'Failed to remove item');
        }

        return $this->cartService->getCartData();
    }

    public function updateQuantity(int $productId, int $quantity): CartData
    {
        $result = $this->cartService->updateQuantity($productId, $quantity);

        if (!$result->isSuccess()) {
            throw new RuntimeException($result->error ?? 'Failed to update quantity');
        }

        return $this->cartService->getCartData();
    }

    public function clearCart(): CartData
    {
        $result = $this->cartService->clearCart();

        if (!$result->isSuccess()) {
            throw new RuntimeException($result->error ?? 'Failed to clear cart');
        }

        return $this->cartService->getCartData();
    }

    public function deleteCart(): void
    {
        $result = $this->cartService->deleteCart();

        if (!$result->isSuccess()) {
            throw new RuntimeException($result->error ?? 'Failed to delete cart');
        }
    }

    public function getCartData(): CartData
    {
        return $this->cartService->getCartData();
    }

    public function getCartCount(): int
    {
        return $this->cartService->getCartCount();
    }

    public function mergeOnLogin(int $userId, string $sessionId): CartOperationResult
    {
        return $this->cartService->mergeOnLogin($userId, $sessionId);
    }

    public function formatCartData(CartData $cartData): array
    {
        return [
            'totalCount' => $cartData->totalCount,
            'totalPrice' => $this->moneyManager->getAmount($cartData->totalPrice),
            'totalPriceFormatted' => $this->moneyManager->format($cartData->totalPrice),
            'currency' => $this->moneyManager->getCurrencyCode(),
            'currencySymbol' => $this->moneyManager->getCurrencySymbol(),
            'items' => array_map(
                fn ($item) => $item instanceof CartItem ? $item->toArray() : $item,
                $cartData->items,
            ),
        ];
    }

    public function getCart(): CartCollection
    {
        return $this->cartService->getCurrentCart();
    }

    public function itemCount(): int
    {
        return $this->getCartCount();
    }

    public function isEmpty(): bool
    {
        return $this->getCartCount() === 0;
    }

    public function getTotalPrice(): Money
    {
        return $this->getCartData()->totalPrice;
    }

    public function getFormattedTotalPrice(): string
    {
        return $this->moneyManager->format($this->getTotalPrice());
    }

    public function hasItem(int $productId): bool
    {
        $cart = $this->getCart();
        return $cart->hasItem($productId);
    }

    public function getItemQuantity(int $productId): int
    {
        $cart = $this->getCart();
        $item = $cart->getItem($productId);
        return $item ? $item->getQuantity() : 0;
    }

    public function getTotalPriceWithTax(): Money
    {
        $total = $this->getTotalPrice();
        if (!$this->taxManager->shouldPriceIncludeTax()) {
            $total = $this->taxManager->addTax($total);
        }
        return $total;
    }

    public function getFormattedTotalPriceWithTax(): string
    {
        return $this->moneyManager->format($this->getTotalPriceWithTax());
    }
}