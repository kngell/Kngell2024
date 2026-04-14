<?php

declare(strict_types=1);

/**
 * Interface for low-level image manipulation
 * Used internally by ImageOptimizer.
 */
interface ImageManipulatorInterface
{
    /**
     * Resize/convert an image from source to target.
     */
    public function manipulate(string $sourcePath, string $targetPath, int $width, array $options = []): bool;

    /**
     * Check if this manipulator supports the given image type.
     */
    public function supports(string $mimeType): bool;
}