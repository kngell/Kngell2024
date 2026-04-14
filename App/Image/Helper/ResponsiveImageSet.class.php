<?php

declare(strict_types=1);

final class ResponsiveImageSet implements ResponsiveImageSetInterface
{
    private array $sizes = [];
    private array $webpSizes = [];

    public function __construct(
        private readonly string $originalPath,
        private readonly array $breakpoints = [640, 1080, 1920, 2560],
    ) {
    }

    public function addSize(int $width, OptimizedImageInterface $image): self
    {
        $this->sizes[$width] = $image;
        return $this;
    }

    public function addWebPSize(int $width, OptimizedImageInterface $image): self
    {
        $this->webpSizes[$width] = $image;
        return $this;
    }

    public function getSizes(): array
    {
        return $this->sizes;
    }

    public function getSize(int $width): ?OptimizedImageInterface
    {
        return $this->sizes[$width] ?? null;
    }

    public function getWebPSizes(): array
    {
        return $this->webpSizes;
    }

    public function getSmallest(): ?OptimizedImageInterface
    {
        return empty($this->sizes) ? null : $this->sizes[min(array_keys($this->sizes))];
    }

    public function getLargest(): ?OptimizedImageInterface
    {
        return empty($this->sizes) ? null : $this->sizes[max(array_keys($this->sizes))];
    }

    public function getSrcSet(string $format = 'original'): string
    {
        $sizes = $format === 'webp' ? $this->webpSizes : $this->sizes;
        $srcSet = [];

        foreach ($sizes as $width => $image) {
            $srcSet[] = sprintf('%s %dw', $image->getUrl(), $width);
        }

        return implode(', ', $srcSet);
    }

    public function getSizesAttribute(string $default = '100vw'): string
    {
        $sizes = [];
        foreach (array_keys($this->sizes) as $width) {
            $sizes[] = sprintf('(max-width: %dpx) %dpx', $width, $width);
        }
        $sizes[] = $default;

        return implode(', ', $sizes);
    }

    public function toArray(): array
    {
        return [
            'original_path' => $this->originalPath,
            'breakpoints' => $this->breakpoints,
            'sizes' => array_map(fn ($img) => $img->toArray(), $this->sizes),
            'webp_sizes' => array_map(fn ($img) => $img->toArray(), $this->webpSizes),
            'srcset' => [
                'original' => $this->getSrcSet('original'),
                'webp' => $this->getSrcSet('webp'),
            ],
            'sizes_attribute' => $this->getSizesAttribute(),
        ];
    }
}