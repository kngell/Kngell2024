<?php

declare(strict_types=1);

class ImageOptimizer implements ImageOptimizerInterface
{
    private array $manipulators = [];

    public function __construct(
        private FileMetadataService $metadataService,
        private ImageCacheInterface $cache,
        private array $defaultBreakpoints = [640, 1080, 1920, 2560],
    ) {
        $this->registerDefaultProcessors();
    }

    public function addFileManipulator(ImageManipulatorInterface $manipulator): self
    {
        $this->manipulators[] = $manipulator;
        return $this;
    }

    public function optimize(?string $imagePath, int $width, array $options = []): OptimizedImageInterface
    {
        $options = new ImageOptimizationOptions($options);
        $imagePath = STORAGE . ltrim($imagePath, DS);

        // Check cache using interface method
        $cached = $this->cache->getOptimizedImage($imagePath, $width, $options->toArray());
        if ($cached) {
            return $cached;
        }

        $metadata = $this->metadataService->createFromWebPath($imagePath);
        if (!$metadata) {
            throw new InvalidArgumentException("Image not found or invalid: {$imagePath}");
        }

        $absolutePath = $this->metadataService->webPathToAbsolutePath($imagePath);
        $fileInfo = new FileInformation($absolutePath);

        $manipulator = $this->findManipulator($metadata['mime_type']);
        if (!$manipulator) {
            throw new RuntimeException("No processor found for image type: {$metadata['mime_type']}");
        }

        $targetPath = $this->generateTargetPath($fileInfo, $width, $options);

        $success = $manipulator->manipulate($absolutePath, $targetPath, $width, $options->toArray());

        if (!$success) {
            throw new RuntimeException("Failed to process image: {$absolutePath}");
        }

        $optimizedFileInfo = new FileInformation($targetPath);
        $imageInfo = $optimizedFileInfo->getImageInfo();

        $aspectRatio = $metadata['image_info']['aspect_ratio'] ??
                      ($imageInfo['width'] > 0 ? $imageInfo['height'] / $imageInfo['width'] : null);

        $optimizedImage = new OptimizedImage(
            path: $targetPath,
            width: $width,
            height: $imageInfo['height'] ?? 0,
            fileSize: $optimizedFileInfo->getSize(),
            mimeType: $optimizedFileInfo->getMimeType(),
            url: $this->generateUrl($targetPath),
            aspectRatio: $aspectRatio,
        );

        // Store in cache using interface method
        $this->cache->storeOptimizedImage($imagePath, $width, $options->toArray(), $optimizedImage);

        return $optimizedImage;
    }

    public function generateWebP(string $imagePath, int $width): OptimizedImageInterface
    {
        return $this->optimize($imagePath, $width, [
            'format' => 'webp',
            'quality' => 80,
        ]);
    }

    public function generateResponsiveImages(string $imagePath, ?array $breakpoints = null): ResponsiveImageSetInterface
    {
        $breakpoints = $breakpoints ?? $this->defaultBreakpoints;

        // Check cache using interface method
        $cached = $this->cache->getResponsiveSet($imagePath, $breakpoints);
        if ($cached) {
            return $cached;
        }

        $metadata = $this->metadataService->createFromWebPath($imagePath);
        if (!$metadata) {
            throw new InvalidArgumentException("Image not found: {$imagePath}");
        }

        $set = new ResponsiveImageSet($imagePath, $breakpoints);

        foreach ($breakpoints as $width) {
            try {
                $optimized = $this->optimize($imagePath, $width);
                $set->addSize($width, $optimized);

                if ($metadata['mime_type'] !== 'image/webp') {
                    $webp = $this->optimize($imagePath, $width, ['format' => 'webp', 'quality' => 80]);
                    $set->addWebPSize($width, $webp);
                }
            } catch (Exception $e) {
                error_log("Failed to generate size {$width}: " . $e->getMessage());
            }
        }

        // Store in cache using interface method
        $this->cache->storeResponsiveSet($imagePath, $breakpoints, $set);

        return $set;
    }

    public function createPlaceholder(string $imagePath, int $width = 20): OptimizedImageInterface
    {
        return $this->optimize($imagePath, $width, [
            'quality' => 30,
            'format' => 'jpg',
            'stripMetadata' => true,
        ]);
    }

    public function getMetadata(string $imagePath): array
    {
        return $this->metadataService->createFromWebPath($imagePath) ?? [];
    }

    public function purgeCache(string $imagePath): bool
    {
        return $this->cache->deleteImageCache($imagePath) > 0;
    }

    public function getCacheStats(): array
    {
        return $this->cache->getStats();
    }

    public function cleanCache(): bool
    {
        return $this->cache->clear();
    }

    private function registerDefaultProcessors(): void
    {
        // Register default image manipulators
        // $this->addFileManipulator(new GdManipulator());
        // $this->addFileManipulator(new ImagickManipulator());
    }

    private function findManipulator(string $mimeType): ?ImageManipulatorInterface
    {
        foreach ($this->manipulators as $manipulator) {
            if ($manipulator->supports($mimeType)) {
                return $manipulator;
            }
        }
        return null;
    }

    private function generateTargetPath(FileInformation $fileInfo, int $width, ImageOptimizationOptions $options): string
    {
        $filename = pathinfo($fileInfo->getFilename(), PATHINFO_FILENAME);
        $cacheDir = $this->cache->getPhysicalPath();

        if (!is_dir($cacheDir)) {
            $oldUmask = umask(0);
            mkdir($cacheDir, 0775, true);
            umask($oldUmask);
        }

        return $cacheDir . $filename . "_{$width}w." . $options->getFormat();
    }

    private function generateUrl(string $path): string
    {
        $relativePath = str_replace(ROOT_DIR, '', $path);
        return str_replace(DS, '/', $relativePath);
    }
}