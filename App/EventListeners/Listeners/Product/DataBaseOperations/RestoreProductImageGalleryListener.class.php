<?php

declare(strict_types=1);

use Psr\Log\LoggerInterface;

final class RestoreProductImageGalleryListener extends AbstractEntityRestoreListener
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

    protected function performRestore(
        SoftDeletableInterface $entity,
        int|string $entityId,
        DateTimeInterface $archivedAt,
        array $payload,
    ): RestoreResultInterface {
        $result = $this->model->restore([
            'product_id' => $entityId,
            'archived_at' => $archivedAt,
        ]);

        $this->assertSuccess($result, 'gallery images', $entityId);

        return new GalleryRestoreResult(
            entityId:     (int) $entityId,
            affectedRows: $result->getRowCount(),
            changed:      $result->hasChanged(),
        );
    }
}