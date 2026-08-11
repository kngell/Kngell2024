<?php

declare(strict_types=1);

final class GalleryDeletionResult implements DeletionResultInterface
{
    use DeletionResultTrait;

    public function __construct(
        private int $entityId,        // product id
        private int $affectedRows,
        private bool $changed,
        private string $deletionMode,
        private string $entityType = 'product',
    ) {
    }
}