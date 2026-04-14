<?php

declare(strict_types=1);

interface ImageCacheInterface
{
    public function generateOptimizedKey(string $imagePath, int $width, array $options = []): string;

    public function getCacheDirectory(): string;

    public function getPhysicalPath(): string;

    public function generateResponsiveKey(string $imagePath, array $breakpoints): string;

    public function storeOptimizedImage(string $imagePath, int $width, array $options, OptimizedImage $image): string;

    public function storeResponsiveSet(string $imagePath, array $breakpoints, ResponsiveImageSet $set): string;

    public function getOptimizedImage(string $imagePath, int $width, array $options = []): ?OptimizedImage;

    public function getResponsiveSet(string $imagePath, array $breakpoints): ?ResponsiveImageSet;

    public function deleteImageCache(string $imagePath): int;

    public function deleteImageVariant(string $key): bool;

    public function getStats(): array;

    public function clear(): bool;
}