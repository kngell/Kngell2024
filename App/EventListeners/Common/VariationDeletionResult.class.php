<?php

declare(strict_types=1);

readonly class VariationDeletionResult
{
    public function __construct(
        public int $productId,
        public bool $changed,
    ) {
    }
}