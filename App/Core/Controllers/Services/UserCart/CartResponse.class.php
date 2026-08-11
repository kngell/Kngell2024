<?php

declare(strict_types=1);

use Brick\Money\Money;

class CartResponse extends AbstractBaseEntityResponse
{
    use EntityDisplayTrait;

    private ?array $cachedItems = null;
    private ?Money $cachedTotalPrice = null;
    private ?array $priceCache = [];

    public function __construct(
        array $image,
        ?UserCartShow $cart,
        bool $isDefault,
        private readonly MoneyManager $moneyManager,
        private readonly ProductPriceService $productPriceService,
    ) {
        parent::__construct($image, $cart, $isDefault);
    }

    public function getEntity(): ?UserCartShow
    {
        return $this->entity;
    }

    public function getItems(): array
    {
        // Load all prices first
        $this->loadAllPrices();

        if (!$this->getEntity()) {
            return [];
        }

        $items = [];
        foreach ($this->getEntity()->getUserCartItem() as $item) {
            $productId = $item->getProductId();
            $priceData = $this->priceCache[$productId] ?? null;

            if (!$priceData) {
                // Fallback: load individual price
                $priceResponse = $this->productPriceService->getPriceForProduct($productId);
                $priceData = $priceResponse->toArray();
            }

            $moneyPrice = $this->moneyManager->createMoney(
                $priceData['basePrice'] ?? 0,
            );
            $totalPrice = $moneyPrice->multipliedBy($item->getQuantity());

            $items[] = [
                'productId' => $productId,
                'quantity' => $item->getQuantity(),
                'price' => $priceData['basePrice'] ?? 0,
                'priceFormatted' => $this->moneyManager->format($moneyPrice),
                'totalPrice' => $this->moneyManager->getAmount($totalPrice),
                'totalPriceFormatted' => $this->moneyManager->format($totalPrice),
                'name' => $priceData['name'] ?? 'Product ' . $productId,
                'imageUrl' => $priceData['imageUrl'] ?? null,
                'variantData' => $item->getVariantData(),
                'onSale' => $priceData['onSale'] ?? false,
                'discountPercent' => $priceData['discountPercent'] ?? null,
            ];
        }

        $this->cachedItems = $items;
        return $items;
    }

    public function getTotalPrice(): Money
    {
        // Use cached total if available
        if ($this->cachedTotalPrice !== null) {
            return $this->cachedTotalPrice;
        }

        // Load all prices first
        $this->loadAllPrices();

        if (!$this->getEntity()) {
            $this->cachedTotalPrice = $this->moneyManager->zero();
            return $this->cachedTotalPrice;
        }

        $total = $this->moneyManager->zero();
        foreach ($this->getEntity()->getUserCartItem() as $item) {
            $productId = $item->getProductId();
            $priceData = $this->priceCache[$productId] ?? null;

            if (!$priceData) {
                // Fallback: load individual price
                $priceResponse = $this->productPriceService->getPriceForProduct($productId);
                $price = $this->moneyManager->createMoney(
                    $priceResponse->getBasePrice() ?? 0,
                );
            } else {
                $price = $this->moneyManager->createMoney(
                    $priceData['basePrice'] ?? 0,
                );
            }

            $total = $total->plus($price->multipliedBy($item->getQuantity()));
        }

        $this->cachedTotalPrice = $total;
        return $total;
    }

    public function getTotalPriceFormatted(): string
    {
        return $this->moneyManager->format($this->getTotalPrice());
    }

    public function getCurrencyCode(): string
    {
        return $this->moneyManager->getCurrencyCode();
    }

    public function isEmpty(): bool
    {
        return $this->getTotalCount() === 0;
    }

    public function getTotalCount(): int
    {
        if (!$this->getEntity()) {
            return 0;
        }

        $total = 0;
        foreach ($this->getEntity()->getUserCartItem() as $item) {
            $total += $item->getQuantity();
        }
        return $total;
    }

    public function toArray(): array
    {
        return [
            'items' => $this->getItems(),
            'totalCount' => $this->getTotalCount(),
            'totalPrice' => $this->moneyManager->getAmount($this->getTotalPrice()),
            'totalPriceFormatted' => $this->getTotalPriceFormatted(),
            'currency' => $this->getCurrencyCode(),
            'isEmpty' => $this->isEmpty(),
            'isDefault' => $this->isDefault,
        ];
    }

    private function loadAllPrices(): void
    {
        if ($this->priceCache !== null) {
            return; // Already loaded
        }

        if (!$this->getEntity()) {
            $this->priceCache = [];
            return;
        }

        $start = microtime(true);

        $productIds = [];
        foreach ($this->getEntity()->getUserCartItem() as $item) {
            $productIds[] = $item->getProductId();
        }

        // Remove duplicates
        $productIds = array_unique($productIds);

        // Batch load all prices in ONE call
        $this->priceCache = $this->productPriceService->getPricesForProducts($productIds);

        $time = (microtime(true) - $start) * 1000;
        error_log(sprintf(
            '[PERFORMANCE] Batch loaded %d product prices in %.2fms',
            count($productIds),
            $time,
        ));
    }
}