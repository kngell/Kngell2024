<?php

declare(strict_types=1);

class ImageProcessor implements FileProcessorInterface
{
    public function __construct(
        private int $maxDimension = 1200,
        private int $quality = 85,
    ) {
    }

    public function supports(FileUpload $file): bool
    {
        return $file->isImage();
    }

    public function process(FileUpload $source, string $targetPath): ?string
    {
        $imageInfo = getimagesize($source->getPathname());
        if ($imageInfo === false) {
            return null;
        }

        [$width, $height, $type] = $imageInfo;

        if (max($width, $height) <= $this->maxDimension) {
            return null;
        }

        return $this->resizeImage($source->getPathname(), $targetPath, $type);
    }

    private function resizeImage(string $sourcePath, string $targetPath, int $imageType): string
    {
        $sourceImage = $this->createImageFromType($sourcePath, $imageType);
        if ($sourceImage === null) {
            throw new FileException('Unsupported image type');
        }

        [$newWidth, $newHeight] = $this->calculateNewDimensions(
            imagesx($sourceImage),
            imagesy($sourceImage),
        );

        $destinationImage = imagecreatetruecolor($newWidth, $newHeight);

        // Preserve transparency for PNG/GIF
        if ($imageType === IMAGETYPE_PNG || $imageType === IMAGETYPE_GIF) {
            imagealphablending($destinationImage, false);
            imagesavealpha($destinationImage, true);
            $transparent = imagecolorallocatealpha($destinationImage, 255, 255, 255, 127);
            imagefilledrectangle($destinationImage, 0, 0, $newWidth, $newHeight, $transparent);
        }

        imagecopyresampled(
            $destinationImage,
            $sourceImage,
            0,
            0,
            0,
            0,
            $newWidth,
            $newHeight,
            imagesx($sourceImage),
            imagesy($sourceImage),
        );

        $this->saveImage($destinationImage, $targetPath, $imageType);

        return $targetPath;
    }

    private function createImageFromType(string $path, int $type): ?GdImage
    {
        return match ($type) {
            IMAGETYPE_JPEG => imagecreatefromjpeg($path),
            IMAGETYPE_PNG => imagecreatefrompng($path),
            IMAGETYPE_GIF => imagecreatefromgif($path),
            IMAGETYPE_WEBP => imagecreatefromwebp($path),
            default => null
        };
    }

    private function calculateNewDimensions(int $width, int $height): array
    {
        $scale = $this->maxDimension / max($width, $height);

        return [
            (int) round($width * $scale),
            (int) round($height * $scale),
        ];
    }

    private function saveImage(GdImage $image, string $path, int $type): void
    {
        $success = match ($type) {
            IMAGETYPE_JPEG => imagejpeg($image, $path, $this->quality),
            IMAGETYPE_PNG => imagepng($image, $path, (int) round(9 * $this->quality / 100)),
            IMAGETYPE_GIF => imagegif($image, $path),
            IMAGETYPE_WEBP => imagewebp($image, $path, $this->quality),
            default => false
        };

        if (!$success) {
            throw new FileException("Failed to save processed image: {$path}");
        }
    }
}