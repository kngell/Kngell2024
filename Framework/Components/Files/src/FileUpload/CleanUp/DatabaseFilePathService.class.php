<?php

declare(strict_types=1);

use Psr\Log\LoggerInterface;

final class DatabaseFilePathService
{
    private ?array $cachedPaths = null;

    public function __construct(
        private ProductModel $productModel,
        private ProductImageGalleryModel $gallery,
        private HeroModel $heroModel,
        private SmallBannerModel $bannerModel,
        private ?LoggerInterface $logger = null,
    ) {
    }

    public function getValidFilePaths(bool $forceRefresh = false): array
    {
        if ($this->cachedPaths !== null && !$forceRefresh) {
            return $this->cachedPaths;
        }

        try {
            // 1. Collect paths from database
            $paths = $this->collectPathsFromDatabase();

            // 2. Normalize and filter paths
            $paths = $this->normalizePaths($paths);

            $this->cachedPaths = $paths;

            if ($this->logger) {
                $this->logger->debug('Collected file paths from database', [
                    'count' => count($paths),
                ]);
            }
        } catch (Exception $e) {
            if ($this->logger) {
                $this->logger->error('Failed to collect file paths from database', [
                    'error' => $e->getMessage(),
                ]);
            }

            $this->cachedPaths = [];
        }

        return $this->cachedPaths;
    }

    /**
     * Clear the cached paths.
     */
    public function clearCache(): void
    {
        $this->cachedPaths = null;
    }

    // =======================================================
    // PRIVATE METHODS
    // =======================================================

    private function collectPathsFromDatabase(): array
    {
        $paths = [];

        $products = $this->productModel->getProductWithPaths();

        foreach ($products as $product) {
            if (!empty($product['main_image'])) {
                $paths[] = $product['main_image'];
            }
            if (!empty($product['main_video'])) {
                $paths[] = $product['main_video'];
            }
        }
        $galleryItems = $this->gallery->getGalleryByPaths();
        foreach ($galleryItems as $item) {
            if (!empty($item['image_url'])) {
                $paths[] = $item['image_url'];
            }
        }

        $heroImgs = $this->heroModel->getImagesPath();
        foreach ($heroImgs as $item) {
            if (!empty($item['image_url'])) {
                $paths[] = $item['image_url'];
            }
        }
        $bannerImgs = $this->bannerModel->getImagesPath();
        foreach ($bannerImgs as $item) {
            if (!empty($item['custom_image_url'])) {
                $paths[] = $item['custom_image_url'];
            }
        }

        return array_filter($paths);
    }

    private function normalizePaths(array $paths): array
    {
        $normalized = [];

        foreach ($paths as $path) {
            $normalizedPath = $this->normalizePath($path);
            if ($normalizedPath) {
                $normalized[] = $normalizedPath;
            }
        }

        return array_values(array_unique($normalized));
    }

    private function normalizePath(string $path): ?string
    {
        $path = trim($path);
        if (empty($path)) {
            return null;
        }

        // Remove leading slash for web paths
        $path = ltrim($path, '/');

        // Replace backslashes with forward slashes
        $path = str_replace('\\', '/', $path);

        // Remove multiple consecutive slashes
        $path = preg_replace('#/+#', '/', $path);

        // ⭐️ CRITICAL FIX: Remove 'uploads/' prefix if present
        // Files on disk are relative to uploads directory, but database stores full paths
        if (strpos($path, 'uploads/') === 0) {
            $path = substr($path, 8); // Remove 'uploads/'
        }

        // Validate path doesn't contain directory traversal
        if (str_contains($path, '../') || str_contains($path, '..\\')) {
            if ($this->logger) {
                $this->logger->warning('Skipping path with directory traversal', [
                    'path' => $path,
                ]);
            }
            return null;
        }

        return $path;
    }
}