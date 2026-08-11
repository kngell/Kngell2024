<?php

declare(strict_types=1);

final class RowHeaderCellConfig
{
    public function __construct(
        public readonly string $checkboxName,
        public readonly Closure $thumbnailExtractor,
        public readonly string $thumbnailAlt,
        public readonly Closure $titleExtractor,
        public readonly ?Closure $subtitleExtractor = null,
    ) {
    }
}