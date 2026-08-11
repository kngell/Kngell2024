<?php

declare(strict_types=1);

use Psr\Log\LoggerInterface;

final class DeleteProductImageGalleryListener extends AbstractEntityDeletionListener
{
    public function __construct(
        private ProductImageGalleryModel $model,
        ?LoggerInterface $logger = null,
    ) {
        parent::__construct($logger);
    }

    protected function expectedEntityClass(): string
    {
        return Product::class;
    }

    protected function entityType(): string
    {
        return 'product';
    }

    protected function performDeletion(
        int|string $entityId,
        string $deletionOption,
        array $payload,
    ): DeletionResultInterface {
        $result = $this->model->delete([
            'product_id' => $entityId,
            'deleteOption' => $deletionOption,
        ]);

        $this->assertSuccess($result, 'gallery images', $entityId);

        return new GalleryDeletionResult(
            entityId:     (int) $entityId,
            affectedRows: $result->getRowCount(),
            changed:      $result->hasChanged(),
            deletionMode: $deletionOption,
        );
    }
}