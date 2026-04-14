<?php

declare(strict_types=1);

class GdManipulator implements ImageManipulatorInterface
{
    private const array SUPPORTED_MIME_TYPES = [
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/webp',
        'image/bmp',
        'image/avif',
    ];

    public function supports(string $mimeType): bool
    {
        return in_array($mimeType, self::SUPPORTED_MIME_TYPES, true);
    }

    public function manipulate(string $sourcePath, string $targetPath, int $width, array $options = []): bool
    {
        $options = new ImageOptimizationOptions($options);

        $imageInfo = getimagesize($sourcePath);
        if ($imageInfo === false) {
            return false;
        }

        [$srcWidth, $srcHeight, $type] = $imageInfo;

        // Calculate new height maintaining aspect ratio
        $newHeight = (int) ($srcHeight * ($width / $srcWidth));

        // Create source image
        $sourceImage = $this->createFromType($sourcePath, $type);
        if (!$sourceImage) {
            return false;
        }

        // Check if image already has transparency
        $hasTransparency = $this->hasTransparency($sourceImage, $type);

        // Only remove background if:
        // 1. Background removal is requested
        // 2. Image doesn't already have transparency
        if ($options->shouldRemoveBackground() && !$hasTransparency) {
            $this->removeBackground($sourceImage, $options);
        }

        // Create destination image
        $destImage = imagecreatetruecolor($width, $newHeight);

        // Handle transparency based on image type and options
        $this->handleTransparency($destImage, $type, $options, $hasTransparency);

        // Disable alpha blending during resize to preserve transparency
        imagealphablending($destImage, false);

        imagecopyresampled(
            $destImage,
            $sourceImage,
            0,
            0,
            0,
            0,
            $width,
            $newHeight,
            $srcWidth,
            $srcHeight,
        );

        imagealphablending($destImage, true);

        // Apply sharpening if requested
        if ($options->getSharpening()) {
            $this->sharpen($destImage);
        }

        // Apply interlacing if requested
        if ($options->getInterlace()) {
            imageinterlace($destImage, true);
        }

        // Ensure target directory exists
        $targetDir = dirname($targetPath);
        if (!is_dir($targetDir)) {
            $oldUmask = umask(0);
            mkdir($targetDir, 0775, true);
            umask($oldUmask);
        }

        // Save
        $result = $this->saveByType($destImage, $targetPath, $type, $options);
        imagedestroy($sourceImage);
        imagedestroy($destImage);
        return $result;
    }

    private function createFromType(string $path, int $type): ?GdImage
    {
        $image = match ($type) {
            IMAGETYPE_JPEG => imagecreatefromjpeg($path),
            IMAGETYPE_PNG => imagecreatefrompng($path),
            IMAGETYPE_GIF => imagecreatefromgif($path),
            IMAGETYPE_WEBP => imagecreatefromwebp($path),
            IMAGETYPE_BMP => imagecreatefrombmp($path),
            IMAGETYPE_AVIF => function_exists('imagecreatefromavif') ? imagecreatefromavif($path) : null,
            default => null,
        };

        // Preserve alpha channel for PNG and WebP
        if ($image && ($type === IMAGETYPE_PNG || $type === IMAGETYPE_WEBP)) {
            imagealphablending($image, false);
            imagesavealpha($image, true);
        }

        return $image;
    }

    /**
     * Check if image already has transparency.
     */
    private function hasTransparency(GdImage $image, int $type): bool
    {
        // JPEG never has transparency
        if ($type === IMAGETYPE_JPEG) {
            return false;
        }

        $width = imagesx($image);
        $height = imagesy($image);
        $totalPixels = $width * $height;

        // Sample pixels to check for transparency
        $sampleSize = min(100, $totalPixels);
        $step = max(1, (int) ($totalPixels / $sampleSize));

        $transparentPixels = 0;
        $totalSampled = 0;

        for ($i = 0; $i < $totalPixels; $i += $step) {
            $x = $i % $width;
            $y = (int) ($i / $width);

            $color = imagecolorat($image, $x, $y);
            $alpha = ($color >> 24) & 0x7F;

            if ($alpha > 0) { // Has some transparency
                $transparentPixels++;
            }
            $totalSampled++;
        }

        // Consider image transparent if more than 1% of sampled pixels have alpha
        $transparencyRatio = $totalSampled > 0 ? $transparentPixels / $totalSampled : 0;

        return $transparencyRatio > 0.01;
    }

    /**
     * Remove background from image (only for images without existing transparency).
     */
    private function removeBackground(GdImage $image, ImageOptimizationOptions $options): void
    {
        $width = imagesx($image);
        $height = imagesy($image);

        // Enable alpha blending for background removal
        imagealphablending($image, false);
        imagesavealpha($image, true);

        // Get the target color to remove
        $targetColor = $options->getBackgroundColor();
        $tolerance = $options->getBackgroundTolerance();

        if ($targetColor) {
            // Remove specific color
            $rgb = $this->hexToRgb($targetColor);
            $targetRgb = imagecolorallocate($image, $rgb['r'], $rgb['g'], $rgb['b']);
        } else {
            // Auto-detect background color from corners
            $targetRgb = $this->detectBackgroundColor($image);
        }

        // Create transparent color
        $transparent = imagecolorallocatealpha($image, 0, 0, 0, 127);

        // Scan and replace background color with transparency
        for ($x = 0; $x < $width; $x++) {
            for ($y = 0; $y < $height; $y++) {
                $currentColor = imagecolorat($image, $x, $y);
                $rgb = imagecolorsforindex($image, $currentColor);

                // Check if this pixel matches the background color
                if ($this->isColorSimilarToRgb($rgb, $targetRgb, $tolerance)) {
                    imagesetpixel($image, $x, $y, $transparent);
                }
            }
        }

        imagealphablending($image, true);
    }

    /**
     * Detect background color from image edges.
     */
    private function detectBackgroundColor(GdImage $image): int
    {
        $width = imagesx($image);
        $height = imagesy($image);

        // Sample corners of the image
        $corners = [
            imagecolorat($image, 0, 0),
            imagecolorat($image, $width - 1, 0),
            imagecolorat($image, 0, $height - 1),
            imagecolorat($image, $width - 1, $height - 1),
        ];

        // Also sample edges
        for ($i = 0; $i < $width; $i += 10) {
            $corners[] = imagecolorat($image, $i, 0); // Top edge
            $corners[] = imagecolorat($image, $i, $height - 1); // Bottom edge
        }

        for ($i = 0; $i < $height; $i += 10) {
            $corners[] = imagecolorat($image, 0, $i); // Left edge
            $corners[] = imagecolorat($image, $width - 1, $i); // Right edge
        }

        // Find the most common color among edges
        $colorCounts = array_count_values($corners);
        arsort($colorCounts);

        return (int) key($colorCounts);
    }

    private function isColorSimilarToRgb(array $color1, int $color2Index, int $tolerance): bool
    {
        static $tempImage = null;
        if (!$tempImage) {
            $tempImage = imagecreatetruecolor(1, 1);
        }

        $color2 = imagecolorsforindex($tempImage, $color2Index);

        $diffR = abs($color1['red'] - $color2['red']);
        $diffG = abs($color1['green'] - $color2['green']);
        $diffB = abs($color1['blue'] - $color2['blue']);

        if ($tolerance === 0) {
            return $diffR === 0 && $diffG === 0 && $diffB === 0;
        }

        return $diffR <= $tolerance && $diffG <= $tolerance && $diffB <= $tolerance;
    }

    private function hexToRgb(string $hex): array
    {
        $hex = ltrim($hex, '#');
        return [
            'r' => (int) hexdec(substr($hex, 0, 2)),
            'g' => (int) hexdec(substr($hex, 2, 2)),
            'b' => (int) hexdec(substr($hex, 4, 2)),
        ];
    }

    private function handleTransparency(GdImage $destImage, int $type, ImageOptimizationOptions $options, bool $sourceHasTransparency = false): void
    {
        $targetFormat = $options->getFormat();
        $willBeTransparent = in_array($targetFormat, ['png', 'webp', 'gif'], true);

        if ($willBeTransparent) {
            // 1. Disable alpha blending to allow literal transparency
            // (rather than blending new colors with the existing canvas)
            imagealphablending($destImage, false);

            // 2. Prepare the transparent color
            $transparent = imagecolorallocatealpha($destImage, 0, 0, 0, 127);

            // 3. Fill the entire canvas with that transparency
            imagefill($destImage, 0, 0, $transparent);

            // 4. Important: Tell GD to save the full alpha channel information
            imagesavealpha($destImage, true);
        } else {
            // Fallback for JPEGs: use a clean white background
            $white = imagecolorallocate($destImage, 255, 255, 255);
            imagefill($destImage, 0, 0, $white);
        }
    }

    private function sharpen(GdImage $image): void
    {
        $sharpenMatrix = [
            [-1, -1, -1],
            [-1, 16, -1],
            [-1, -1, -1],
        ];
        $divisor = 8;
        $offset = 0;
        imageconvolution($image, $sharpenMatrix, $divisor, $offset);
    }

    private function saveByType(GdImage $image, string $path, int $type, ImageOptimizationOptions $options): bool
    {
        $quality = $options->getQuality();
        $format = $options->getFormat();

        // If format is specified, override type
        $targetType = match ($format) {
            'jpg', 'jpeg' => IMAGETYPE_JPEG,
            'png' => IMAGETYPE_PNG,
            'gif' => IMAGETYPE_GIF,
            'webp' => IMAGETYPE_WEBP,
            'bmp' => IMAGETYPE_BMP,
            'avif' => IMAGETYPE_AVIF,
            default => $type,
        };

        return match ($targetType) {
            IMAGETYPE_JPEG => imagejpeg($image, $path, $quality),
            IMAGETYPE_PNG => imagepng($image, $path, (int) round(9 - ($quality / 11.11))),
            IMAGETYPE_GIF => imagegif($image, $path),
            IMAGETYPE_WEBP => imagewebp($image, $path, $quality),
            IMAGETYPE_BMP => imagebmp($image, $path),
            IMAGETYPE_AVIF => function_exists('imageavif') ? imageavif($image, $path, $quality) : false,
            default => false,
        };
    }
}