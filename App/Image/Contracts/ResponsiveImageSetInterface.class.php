<?php

declare(strict_types=1);
interface ResponsiveImageSetInterface
{
    public function addSize(int $width, OptimizedImageInterface $image): self;

    public function addWebPSize(int $width, OptimizedImageInterface $image): self;

    public function getSizes(): array;

    public function getSize(int $width): ?OptimizedImageInterface;

    public function getWebPSizes(): array;

    public function getSmallest(): ?OptimizedImageInterface;

    public function getLargest(): ?OptimizedImageInterface;

    public function getSrcSet(string $format = 'original'): string;

    public function getSizesAttribute(string $default = '100vw'): string;

    public function toArray(): array;
}