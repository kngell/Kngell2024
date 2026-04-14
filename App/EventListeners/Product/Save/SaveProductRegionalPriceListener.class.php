<?php

declare(strict_types=1);

class SaveProductRegionalPriceListener implements EventListenerInterface
{
    public function __construct(
        private ProductRegionalPriceModel $productprice,
        private RegionContextInterface $regionContext,
    ) {
    }

    public function handle(EventInterface $event): ?object
    {
        $payload = $event->getParams();
        $productId = $payload['product_id'];
        $regionCode = strtolower($this->regionContext->getRegionCode());
        $formData = $payload['form_data'];
        $criteria = [
            'product_id' => $productId,
            'region_code' => $regionCode,
        ];
        $data = $this->getPriceData($productId, $regionCode, $formData);
        return $this->productprice->save($data, $criteria);
    }

    private function getPriceData(int|null $productId = null, string $regionCode = '', array $formData = []): array
    {
        $data = [];
        if (isset($formData['price_id']) && (int) $formData['price_id'] !== 0) {
            $data['price_id'] = $formData['price_id'];
        }
        $data['product_id'] = $productId;
        $data['region_code'] = strtolower($regionCode);
        $data['currency_id'] = $formData['base_currency_id'] ?? null;
        $data['base_price'] = $formData['base_price'] ?? null;
        $data['compare_price'] = $formData['compare_price'] ?? null;
        $data['cost_price'] = $formData['cost_price'] ?? null;
        $data['sale_price'] = $formData['sale_price'] ?? null;
        $data['price_includes_tax'] = $formData['price_includes_tax'] ?? null;
        return $data;
    }
}