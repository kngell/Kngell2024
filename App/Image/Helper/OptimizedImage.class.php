<?php

declare(strict_types=1);

final class OptimizedImage implements OptimizedImageInterface
{
    public function __construct(
        private readonly string $path,
        private readonly int $width,
        private readonly int $height,
        private readonly int $fileSize,
        private readonly string $mimeType,
        private readonly string $url,
        private readonly ?float $aspectRatio = null,
    ) {
    }

    public function __toString(): string
    {
        return $this->getUrl();
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getWidth(): int
    {
        return $this->width;
    }

    public function getHeight(): int
    {
        return $this->height;
    }

    public function getFileSize(): int
    {
        return $this->fileSize;
    }

    public function getFormattedFileSize(): string
    {
        return $this->formatBytes($this->fileSize);
    }

    public function getMimeType(): string
    {
        return $this->mimeType;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getAspectRatio(): float
    {
        return $this->aspectRatio ?? ($this->height > 0 ? round($this->width / $this->height, 2) : 1);
    }

    public function toArray(): array
    {
        return [
            'path' => $this->path,
            'width' => $this->width,
            'height' => $this->height,
            'fileSize' => $this->fileSize,
            'formattedSize' => $this->getFormattedFileSize(),
            'mimeType' => $this->mimeType,
            'url' => $this->url,
            'aspectRatio' => $this->getAspectRatio(),
        ];
    }

    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);

        return round($bytes, 2) . ' ' . $units[$pow];
    }
}