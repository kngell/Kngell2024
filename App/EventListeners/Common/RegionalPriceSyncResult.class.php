<?php

declare(strict_types=1);

readonly class RegionalPriceSyncResult
{
    public function __construct(
        public int $productId,
        public string $regionCode,
        public bool $changed,
    ) {
    }
}