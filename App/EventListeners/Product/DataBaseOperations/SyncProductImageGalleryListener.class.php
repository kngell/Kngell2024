<?php

declare(strict_types=1);

class SyncProductImageGalleryListener implements EventListenerInterface
{
    public function __construct(
        private ProductImageGalleryModel $model,
    ) {
    }

    public function handle(EventInterface $event): ?object
    {
        $payload = $event->getParams();

        // --------------------------------------------------
        // 1. Validate & extract payload
        // --------------------------------------------------
        $productId = (int) ($payload['product_id'] ?? 0);
        if ($productId <= 0) {
            throw new InvalidArgumentException(
                'SyncProductImageGalleryListener requires a valid product_id.',
            );
        }

        $currentUrls = $this->sanitizeUrls(
            $payload['media']['img_gallery'] ?? [],
        );

        $productName = trim($payload['form_data']['name'] ?? '');
        if ($productName === '') {
            $productName = 'Product';
        }

        // --------------------------------------------------
        // 2. Fetch existing records and build lookup map
        // --------------------------------------------------
        $existingRecords = $this->model
            ->all(['product_id' => $productId])
            ->asArray();

        // Map: image_url => full record (preserves editable fields like alt_text)
        $existingMap = [];
        foreach ($existingRecords as $record) {
            $existingMap[$record['image_url']] = $record;
        }

        // --------------------------------------------------
        // 3. Delete orphaned URLs (in DB but not in submission)
        // --------------------------------------------------
        $urlsInDb = array_keys($existingMap);
        $urlsToDelete = array_diff($urlsInDb, $currentUrls);
        $deleteResult = null;

        if (!empty($urlsToDelete)) {
            $deleteResult = $this->model->delete([
                'product_id' => $productId,
                'image_url' => $urlsToDelete,
            ]);

            if (!$deleteResult->isSuccess()) {
                throw new RuntimeException(
                    "Failed to remove orphan gallery images for product {$productId}.",
                );
            }
        }

        // --------------------------------------------------
        // 4. Upsert current URLs
        // --------------------------------------------------
        if (empty($currentUrls)) {
            return $deleteResult;
        }

        $toSync = [];
        foreach ($currentUrls as $index => $url) {
            $sortOrder = $index + 1;
            $existing = $existingMap[$url] ?? null;

            $row = [
                'product_id' => $productId,
                'image_url' => $url,
                'sort_order' => $sortOrder,
                // Preserve user-edited alt_text; only generate a default for new entries
                'alt_text' => $existing['alt_text']
                    ?? "{$productName} gallery image {$sortOrder}",
            ];

            if ($existing !== null) {
                $row['id'] = $existing['id'];
            }

            $toSync[] = $row;
        }

        $saveResult = $this->model->save($toSync);

        if (!$saveResult->isSuccess()) {
            throw new RuntimeException(
                "Failed to synchronize gallery images for product {$productId}.",
            );
        }

        return $saveResult->hasChanged() ? $saveResult : $deleteResult;
    }

    /**
     * Filter out any non-string, empty, or whitespace-only URLs
     * and re-index to maintain correct sort_order.
     */
    private function sanitizeUrls(mixed $urls): array
    {
        if (!is_array($urls)) {
            return [];
        }

        return array_values(
            array_filter(
                $urls,
                fn (mixed $url): bool => is_string($url) && trim($url) !== '',
            ),
        );
    }
}