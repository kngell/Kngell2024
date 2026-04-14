<?php

declare(strict_types=1);

final class ImageOptimizationOptions
{
    private array $options;

    public function __construct(array $options = [])
    {
        $this->options = array_merge([
            'quality' => 85,
            'format' => 'png',
            'stripMetadata' => true,
            'sharpening' => false,
            'interlace' => true,
            'removeBackground' => true,      // New: Remove background
            'backgroundColor' => null,        // New: Specific color to remove (e.g., '#FFFFFF')
            'backgroundTolerance' => 30,      // New: Color matching tolerance
        ], $options);
    }

    public function getQuality(): int
    {
        return $this->options['quality'];
    }

    public function getFormat(): string
    {
        return $this->options['format'];
    }

    public function shouldStripMetadata(): bool
    {
        return $this->options['stripMetadata'];
    }

    public function getSharpening(): bool
    {
        return $this->options['sharpening'];
    }

    public function getInterlace(): bool
    {
        return $this->options['interlace'];
    }

    public function shouldRemoveBackground(): bool
    {
        return $this->options['removeBackground'];
    }

    public function getBackgroundColor(): ?string
    {
        return $this->options['backgroundColor'];
    }

    public function getBackgroundTolerance(): int
    {
        return $this->options['backgroundTolerance'];
    }

    public function withQuality(int $quality): self
    {
        $clone = clone $this;
        $clone->options['quality'] = min(100, max(1, $quality));
        return $clone;
    }

    public function withFormat(string $format): self
    {
        $clone = clone $this;
        $clone->options['format'] = $format;
        return $clone;
    }

    public function withBackgroundRemoval(?string $color = null, int $tolerance = 30): self
    {
        $clone = clone $this;
        $clone->options['removeBackground'] = true;
        $clone->options['backgroundColor'] = $color;
        $clone->options['backgroundTolerance'] = $tolerance;
        return $clone;
    }

    public function toArray(): array
    {
        return $this->options;
    }
}