<?php

declare(strict_types=1);

class SaveProductRegionalPriceListener implements EventListenerInterface
{
    public function __construct(
        private ProductRegionalPriceModel $productprice,
        private RegionContextInterface $regionContext,
    ) {
    }

    public function update(EventInterface $event): ?object
    {
        $payload = $event->getParams();
        $productId = $payload['product_id'];
        $regionCode = $this->regionContext->getRegionCode();
        $formData = $payload['form_data'];

        $em = $this->productprice->getEntityManager();
        $em->beginTransaction();

        try {
            $data['product_id'] = $productId;
            $data['region_code'] = strtolower($regionCode);
            $data['currency_id'] = $formData['base_currency_id'] ?? null;
            $data['base_price'] = $formData['base_price'] ?? null;
            $data['compare_price'] = $formData['compare_price'] ?? null;
            $data['cost_price'] = $formData['cost_price'] ?? null;
            $data['sale_price'] = $formData['sale_price'] ?? null;
            $data['price_includes_tax'] = $formData['price_includes_tax'] ?? null;

            /** @var QueryResult */
            $insertResult = $this->productprice->save($data);
            if (!$insertResult->isSuccess()) {
                throw new RuntimeException('Failed to insert product variation row.');
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