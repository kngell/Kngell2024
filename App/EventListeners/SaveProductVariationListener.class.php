<?php

declare(strict_types=1);

class SaveProductVariationListener implements EventListenerInterface
{
    public function __construct(
        private ProductVariationModel $productVariationModel,
        private VariationAttributeModel $variationAttributeModel,
    ) {
    }

    public function update(EventInterface $event): ?object
    {
        $payload = $event->getParams();
        $productId = $payload['product_id'];
        $variationsData = $payload['form_data']['variations'] ?? [];

        if (empty($variationsData)) {
            return null;
        }

        $em = $this->productVariationModel->getEntityManager();
        $em->beginTransaction();

        try {
            foreach ($variationsData as $variation) {
                $attributes = $variation['attributes'] ?? [];
                unset($variation['attributes']);

                $variation['product_id'] = $productId;

                if (!isset($variation['stock_status_id'])) {
                    $variation['stock_status_id'] = 1;
                }
                /** @var QueryResult */
                $insertResult = $this->productVariationModel->save($variation);
                if (!$insertResult->isSuccess()) {
                    throw new RuntimeException('Failed to insert product variation row.');
                }

                $variationId = $insertResult->getLastInsertId();
                $attributeData = [];
                foreach ($attributes as $attribute) {
                    $attributeData[] = [
                        'variation_id' => $variationId,
                        'attribute_name' => $attribute['attribute_name'] ?? null,
                        'attribute_value' => $attribute['attribute_value'] ?? null,
                    ];
                }
                $attrResult = $this->variationAttributeModel->save($attributeData);
                if (!$attrResult->isSuccess()) {
                    throw new RuntimeException('Failed to insert variation attribute row.');
                }
            }

            $em->commit();
        } catch (Throwable $e) {
            $em->rollback();
            error_log('Variation save failed and rolled back: ' . $e->getMessage());
            return null;
        }

        return null;
    }
}