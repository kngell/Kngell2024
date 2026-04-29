<?php

declare(strict_types=1);

class DeleteProductRegionalPriceListener implements EventListenerInterface
{
    public function __construct(
        private ProductRegionalPriceModel $productPrice,
        private RegionContextInterface $regionContext,
    ) {
    }

    public function handle(EventInterface $event): RegionalPriceDeletionResult
    {
        $payload = $event->getParams();

        /** @var Product */
        $record = $payload['record'] ?? null;
        $productId = (int) ($payload['product_id'] ?? $record->getEntityPrimarykeyValue() ?? 0);

        if ($productId <= 0) {
            throw new InvalidArgumentException(
                'DeleteProductRegionalPriceListener requires a valid product_id.',
            );
        }

        $regionCode = strtolower($this->regionContext->getRegionCode());
        $deletionOption = $payload['deletion_option'] ?? null;

        $result = $this->productPrice->delete([
            'product_id' => $productId,
            'region_code' => $regionCode,
            'deleteOption' => $deletionOption,
        ]);

        if (!$result->isSuccess()) {
            throw new RuntimeException(
                "Failed to delete regional price for product {$productId} in region {$regionCode}.",
            );
        }

        return new RegionalPriceDeletionResult(
            productId:  $productId,
            regionCode: $regionCode,
            changed:    $result->hasChanged(),
        );
    }
}