<?php

declare(strict_types=1);

class ImageProcessor implements FileProcessorInterface
{
    public function __construct(
        private int $maxDimension = 1200,
    ) {
    }

    public function supports(FileUpload $file): bool
    {
        return $file->isImage();
    }

    public function process(FileUpload $source, string $targetPath): ?string
    {
        // Only resize if image is HUGE (e.g., > 2500px)
        // This prevents double-processing common sizes

        $imageInfo = getimagesize($source->getPathname());
        if ($imageInfo === false) {
            return null;
        }

        [$width, $height, $type] = $imageInfo;

        // Only resize if image is larger than the largest breakpoint
        if (max($width, $height) <= $this->maxDimension) {
            return null; // Let optimizer handle it
        }

        // Just copy, don't resize - let optimizer do the work
        copy($source->getPathname(), $targetPath);
        return $targetPath;
    }
}