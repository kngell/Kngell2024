<?php

declare(strict_types=1);

class DeleteProductImageGalleryListener implements EventListenerInterface
{
    public function __construct(
        private ProductImageGalleryModel $model,
    ) {
    }

    public function handle(EventInterface $event): GalleryDeletionResult
    {
        $payload = $event->getParams();

        /** @var Product */
        $record = $payload['record'] ?? null;
        $productId = (int) ($payload['product_id'] ?? $record->getEntityPrimarykeyValue() ?? 0);

        if ($productId <= 0) {
            throw new InvalidArgumentException(
                'DeleteProductImageGalleryListener requires a valid product_id.',
            );
        }

        $deleteOption = $payload['deletion_option'] ?? null;

        if ($deleteOption === null) {
            $deletionOption = 'permanent';
        }

        $result = $this->model->delete([
            'product_id' => $productId,
            'deleteOption' => $deleteOption,
        ]);

        if (!$result->isSuccess()) {
            throw new RuntimeException(
                "Failed to delete gallery images for product {$productId}.",
            );
        }

        return new GalleryDeletionResult(
            productId:    $productId,
            affectedRows: $result->getRowCount(),
            changed:      $result->hasChanged(),
            deletionMode: $deletionOption ?? 'default',
        );
    }
}