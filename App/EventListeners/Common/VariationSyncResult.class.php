<?php

declare(strict_types=1);

readonly class VariationSyncResult
{
    public function __construct(
        public int $productId,
        public int $variationsDeleted,
        public int $variationsSynced,
        public int $attributesSynced,
        public bool $changed,
    ) {
    }
}