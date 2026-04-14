<?php

declare(strict_types=1);

interface ImageOptimizerInterface
{
    public function optimize(string $imagePath, int $width, array $options = []): OptimizedImageInterface;

    public function generateWebP(string $imagePath, int $width): OptimizedImageInterface;

    public function generateResponsiveImages(string $imagePath, ?array $breakpoints = null): ResponsiveImageSetInterface;

    public function createPlaceholder(string $imagePath, int $width = 20): OptimizedImageInterface;

    public function getMetadata(string $imagePath): array;

    public function purgeCache(string $imagePath): bool;

    public function getCacheStats(): array;

    public function cleanCache(): bool;
}