<?php

declare(strict_types=1);

class DeleteProductVariationListener implements EventListenerInterface
{
    public function __construct(
        private ProductVariationModel $productVariationModel,
        private VariationAttributeModel $variationAttributeModel,
    ) {
    }

    public function handle(EventInterface $event): VariationDeletionResult
    {
        $payload = $event->getParams();
        /** @var Product */
        $record = $payload['record'] ?? null;
        $productId = (int) ($payload['product_id'] ?? $record->getEntityPrimarykeyValue() ?? 0);

        if ($productId <= 0) {
            throw new InvalidArgumentException(
                'DeleteProductVariationListener requires a valid product_id.',
            );
        }

        $deletionOption = $payload['deletion_option'] ?? null;
        $changed = false;

        // --------------------------------------------------
        // 1. Collect variation IDs to cascade to attributes
        // --------------------------------------------------
        $existingVariations = $this->productVariationModel
            ->all(['product_id' => $productId])
            ->asArray();

        $variationIds = array_column($existingVariations, 'id');

        // --------------------------------------------------
        // 2. Delete attributes first (child records)
        // --------------------------------------------------
        if (!empty($variationIds)) {
            $attrResult = $this->variationAttributeModel->delete([
                'variation_id' => $variationIds,
                'deleteOption' => $deletionOption,
            ]);

            if (!$attrResult->isSuccess()) {
                throw new RuntimeException(
                    "Failed to delete variation attributes for product {$productId}.",
                );
            }

            if ($attrResult->hasChanged()) {
                $changed = true;
            }
        }

        // --------------------------------------------------
        // 3. Delete variations (parent records)
        // --------------------------------------------------
        $varResult = $this->productVariationModel->delete([
            'product_id' => $productId,
            'deleteOption' => $deletionOption,
        ]);

        if (!$varResult->isSuccess()) {
            throw new RuntimeException(
                "Failed to delete variations for product {$productId}.",
            );
        }

        if ($varResult->hasChanged()) {
            $changed = true;
        }

        return new VariationDeletionResult(
            productId: $productId,
            changed:   $changed,
        );
    }
}