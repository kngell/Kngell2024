<?php

declare(strict_types=1);
interface OptimizedImageInterface
{
    public function getPath(): string;

    public function getWidth(): int;

    public function getHeight(): int;

    public function getFileSize(): int;

    public function getFormattedFileSize(): string;

    public function getMimeType(): string;

    public function getUrl(): string;

    public function getAspectRatio(): float;

    public function toArray(): array;
}