<?php

declare(strict_types=1);
class SaveProductVariationListener implements EventListenerInterface
{
    public function __construct(
        private ProductVariationModel $productVariationModel,
        private VariationAttributeModel $variationAttributeModel,
    ) {
    }

    public function handle(EventInterface $event): ?object
    {
        $payload = $event->getParams();
        if ($payload['was_skipped'] && empty($data['form_data']['variations'])) {
            return null;
        }
        $productId = $payload['product_id'];
        $variationsData = $payload['form_data']['variations'] ?? [];

        $existingVariations = $this->productVariationModel->all(['product_id' => $productId]);
        $existingIds = array_column($existingVariations->asArray(), 'id');

        $submittedIds = array_filter(array_column($variationsData, 'id'));
        $idsToDelete = array_diff($existingIds, $submittedIds);

        $finalResult = null;
        if (!empty($idsToDelete)) {
            $deleteResult = $this->productVariationModel->delete(['id' => $idsToDelete]);
            if (!$deleteResult->isSuccess()) {
                throw new RuntimeException('Failed to remove deleted variations.');
            }
            $finalResult = $deleteResult;
        }

        if (empty($variationsData)) {
            return $finalResult;
        }

        $allAttributesToSync = [];

        foreach ($variationsData as $variation) {
            $attributes = $variation['attributes'] ?? [];
            unset($variation['attributes']);

            $variation['product_id'] = $productId;
            if (!isset($variation['stock_status_id'])) {
                $variation['stock_status_id'] = 1;
            }
            $save = $this->productVariationModel->save($variation);

            if (!$save->isSuccess()) {
                throw new RuntimeException('Failed to process product variation.');
            }
            if ($save->hasChanged()) {
                $finalResult = $save;
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
                if (!empty($attribute['id']) && (int) $attribute['id'] !== 0) {
                    $attrRow['id'] = $attribute['id'];
                }

                $allAttributesToSync[] = $attrRow;
            }
        }

        if (!empty($allAttributesToSync)) {
            $attrResult = $this->variationAttributeModel->save($allAttributesToSync);
            if (!$attrResult->isSuccess()) {
                throw new RuntimeException('Failed to process variation attributes.');
            }
            if ($attrResult->hasChanged() && ($finalResult === null || !$finalResult->hasChanged())) {
                $finalResult = $attrResult;
            }
        }

        return $finalResult;
    }
}