<?php

declare(strict_types=1);

readonly class GallerySyncResult
{
    public function __construct(
        public int $productId,
        public int $deleted,
        public int $synced,
        public bool $changed,
    ) {
    }
}