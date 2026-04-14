<?php

declare(strict_types=1);

class SaveProductImageGalleryListener implements EventListenerInterface
{
    public function __construct(
        private ProductImageGalleryModel $model,
    ) {
    }

    public function handle(EventInterface $event): ?object
    {
        $payload = $event->getParams();
        $productId = (int) $payload['product_id'];
        $currentUrls = $payload['media']['img_gallery'] ?? [];
        $productName = $payload['form_data']['name'] ?? 'Product';

        $finalResult = null;

        $existingRecords = $this->model->all(['product_id' => $productId])->asArray();
        $existingMap = array_column($existingRecords, 'id', 'image_url');

        $urlsInDb = array_keys($existingMap);
        $urlsToDelete = array_diff($urlsInDb, $currentUrls);

        if (!empty($urlsToDelete)) {
            $deleteResult = $this->model->delete([
                'product_id' => $productId,
                'image_url' => $urlsToDelete,
            ]);

            if (!$deleteResult->isSuccess()) {
                throw new RuntimeException('Failed to remove orphan gallery images.');
            }

            $finalResult = $deleteResult;
        }

        if (empty($currentUrls)) {
            return $finalResult;
        }

        $toSync = [];
        foreach ($currentUrls as $index => $url) {
            $sortOrder = $index + 1;
            $row = [
                'product_id' => $productId,
                'image_url' => $url,
                'sort_order' => $sortOrder,
                'alt_text' => "{$productName} gallery image {$sortOrder}",
            ];

            if (isset($existingMap[$url])) {
                $row['id'] = $existingMap[$url];
            }
            $toSync[] = $row;
        }

        $saveResult = $this->model->save($toSync);

        if (!$saveResult->isSuccess()) {
            throw new RuntimeException('Failed to synchronize product gallery.');
        }

        return ($saveResult->hasChanged()) ? $saveResult : $finalResult;
    }
}