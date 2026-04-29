<?php

declare(strict_types=1);

readonly class GalleryDeletionResult
{
    public function __construct(
        public int $productId,
        public int $affectedRows,
        public bool $changed,
        public string $deletionMode,
    ) {
    }
}