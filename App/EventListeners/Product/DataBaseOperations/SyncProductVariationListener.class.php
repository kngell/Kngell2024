<?php

declare(strict_types=1);

class SyncProductVariationListener implements EventListenerInterface
{
    public function __construct(
        private ProductVariationModel $productVariationModel,
        private VariationAttributeModel $variationAttributeModel,
    ) {
    }

    public function handle(EventInterface $event): VariationSyncResult
    {
        $payload = $event->getParams();

        $productId = (int) ($payload['product_id'] ?? 0);
        if ($productId <= 0) {
            throw new InvalidArgumentException(
                'SyncProductVariationListener requires a valid product_id.',
            );
        }

        $variationsData = $payload['form_data']['variations'] ?? [];
        $wasSkipped = (bool) ($payload['was_skipped'] ?? false);

        if ($wasSkipped && empty($variationsData)) {
            return new VariationSyncResult(
                productId:          $productId,
                variationsDeleted:  0,
                variationsSynced:   0,
                attributesSynced:   0,
                changed:            false,
            );
        }

        // --------------------------------------------------
        // 1. Delete removed variations
        // --------------------------------------------------
        $existingVariations = $this->productVariationModel
            ->all(['product_id' => $productId])
            ->asArray();

        $existingIds = array_column($existingVariations, 'id');
        $submittedIds = array_filter(array_column($variationsData, 'id'));
        $idsToDelete = array_diff($existingIds, $submittedIds);

        $changed = false;

        if (!empty($idsToDelete)) {
            $deleteResult = $this->productVariationModel->delete([
                'id' => $idsToDelete,
            ]);

            if (!$deleteResult->isSuccess()) {
                throw new RuntimeException(
                    "Failed to remove deleted variations for product {$productId}.",
                );
            }

            $changed = $deleteResult->hasChanged();
        }

        // --------------------------------------------------
        // 2. Upsert variations and collect attributes
        // --------------------------------------------------
        $allAttributesToSync = [];

        foreach ($variationsData as $variation) {
            $attributes = $variation['attributes'] ?? [];
            unset($variation['attributes']);

            $variation['product_id'] = $productId;
            $variation['stock_status_id'] ??= 1;

            $save = $this->productVariationModel->save($variation);

            if (!$save->isSuccess()) {
                throw new RuntimeException(
                    "Failed to save variation for product {$productId}.",
                );
            }

            if ($save->hasChanged()) {
                $changed = true;
            }

            $variationId = $save->isInsertOperation()
                ? $save->getLastInsertId()
                : $save->getLastUpdateId();

            foreach ($attributes as $attribute) {
                $attrRow = [
                    'variation_id' => $variationId,
                    'attribute_name' => $attribute['attribute_name'] ?? null,
                    'attribute_value' => $attribute['attribute_value'] ?? null,
                ];

                $attrId = (int) ($attribute['id'] ?? 0);
                if ($attrId > 0) {
                    $attrRow['id'] = $attrId;
                }

                $allAttributesToSync[] = $attrRow;
            }
        }

        // --------------------------------------------------
        // 3. Bulk-save all attributes
        // --------------------------------------------------
        if (!empty($allAttributesToSync)) {
            $attrResult = $this->variationAttributeModel->save($allAttributesToSync);

            if (!$attrResult->isSuccess()) {
                throw new RuntimeException(
                    "Failed to save variation attributes for product {$productId}.",
                );
            }

            if ($attrResult->hasChanged()) {
                $changed = true;
            }
        }

        return new VariationSyncResult(
            productId:         $productId,
            variationsDeleted: count($idsToDelete),
            variationsSynced:  count($variationsData),
            attributesSynced:  count($allAttributesToSync),
            changed:           $changed,
        );
    }
}