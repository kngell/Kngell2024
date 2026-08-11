<?php

declare(strict_types=1);

class SyncProductRegionalPriceListener implements EventListenerInterface
{
    public function __construct(
        private ProductRegionalPriceModel $productPrice,
        private RegionContextInterface $regionContext,
    ) {
    }

    public function handle(EventInterface $event): RegionalPriceSyncResult
    {
        $productId = (int) ($event->getData()->getEntityId() ?? 0);
        if ($productId <= 0) {
            throw new InvalidArgumentException(
                'SyncProductRegionalPriceListener requires a valid product_id.',
            );
        }

        $regionCode = strtolower($this->regionContext->getRegionCode());
        if ($regionCode === '') {
            throw new InvalidArgumentException(
                'SyncProductRegionalPriceListener requires a valid region code.',
            );
        }

        $formData = $event->getData()->getFormData() ?? [];
        $criteria = [
            'product_id' => $productId,
            'region_code' => $regionCode,
        ];

        $data = $this->buildPriceData($productId, $regionCode, $formData);
        $result = $this->productPrice->save($data, $criteria);

        if (!$result->isSuccess()) {
            throw new RuntimeException(
                "Failed to save regional price for product {$productId} in region {$regionCode}.",
            );
        }

        return new RegionalPriceSyncResult(
            productId:  $productId,
            regionCode: $regionCode,
            changed:    $result->hasChanged(),
        );
    }

    private function buildPriceData(int $productId, string $regionCode, array $formData): array
    {
        $data = [
            'product_id' => $productId,
            'region_code' => $regionCode,
            'currency_id' => $formData['base_currency_id'] ?? null,
            'base_price' => $formData['base_price'] ?? null,
            'compare_price' => $formData['compare_price'] ?? null,
            'cost_price' => $formData['cost_price'] ?? null,
            'sale_price' => $formData['sale_price'] ?? null,
            'price_includes_tax' => $formData['price_includes_tax'] ?? null,
        ];

        $priceId = (int) ($formData['price_id'] ?? 0);
        if ($priceId > 0) {
            $data['price_id'] = $priceId;
        }

        return $data;
    }
}