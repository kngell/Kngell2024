<?php

declare(strict_types=1);

class ImageCache implements ImageCacheInterface
{
    private const string VARIANT_LIST_SUFFIX = '_variants';
    private const string OPTIMIZED_PREFIX = 'optimized_';
    private const string RESPONSIVE_PREFIX = 'responsive_';
    private const string BASE_PREFIX = 'img_';

    public function __construct(
        private CacheInterface $cache,
        private FileOperationsInterface $fileOps,
        private FileMetadataService $metadataService,
        private string $physicalPath,
    ) {
    }

    public function generateOptimizedKey(string $imagePath, int $width, array $options = []): string
    {
        $metadata = $this->metadataService->createFromWebPath($imagePath) ?? [];
        $absolutePath = $this->metadataService->webPathToAbsolutePath($imagePath);
        if (!$absolutePath) {
            return '';
        }
        $fileInfo = new FileInformation($absolutePath);

        $data = [
            'path' => $fileInfo->getPathname(),
            'width' => $width,
            'options' => $options,
            'modified' => $metadata['modified_at'] ?? $fileInfo->getMTime(),
            'size' => $metadata['size'] ?? $fileInfo->getSize(),
            'hash' => $metadata['hash'] ?? $fileInfo->getHash('md5'),
        ];

        // Extract filename and sanitize it first
        $rawFilename = pathinfo($fileInfo->getFilename(), PATHINFO_FILENAME);
        $cleanFilename = preg_replace('/[^a-zA-Z0-9]/', '_', $rawFilename);

        if (strlen($cleanFilename) > 20) {
            $cleanFilename = substr($cleanFilename, 0, 20);
        }

        $hash = md5(serialize($data));
        $key = self::OPTIMIZED_PREFIX . $hash . '_' . $cleanFilename . '_' . $width . 'w';

        // Final sanitization
        return $this->sanitizeKey($key);
    }

    public function generateResponsiveKey(string $imagePath, array $breakpoints): string
    {
        $metadata = $this->metadataService->createFromWebPath($imagePath) ?? [];
        $data = [
            'path' => $imagePath,
            'breakpoints' => $breakpoints,
            'modified' => $metadata['modified_at'] ?? time(),
        ];

        $key = self::RESPONSIVE_PREFIX . md5(serialize($data));
        return $this->sanitizeKey($key);
    }

    public function storeOptimizedImage(string $imagePath, int $width, array $options, OptimizedImage $image): string
    {
        $key = $this->generateOptimizedKey($imagePath, $width, $options);

        // Store the image
        $this->safeSet($key, $image, ['images', 'optimized']);

        // Track this variant
        $baseKey = $this->generateBaseKey($imagePath);
        $listKey = $this->getVariantListKey($baseKey);

        $variants = $this->safeGet($listKey, []);

        if (!in_array($key, $variants)) {
            $variants[] = $key;
            $this->safeSet($listKey, $variants);
        }

        return $key;
    }

    public function storeResponsiveSet(string $imagePath, array $breakpoints, ResponsiveImageSet $set): string
    {
        $key = $this->generateResponsiveKey($imagePath, $breakpoints);

        // Store the set
        $this->safeSet($key, $set, ['images', 'responsive_sets']);

        // Track this variant
        $baseKey = $this->generateBaseKey($imagePath);
        $listKey = $this->getVariantListKey($baseKey);

        $variants = $this->safeGet($listKey, []);

        if (!in_array($key, $variants)) {
            $variants[] = $key;
            $this->safeSet($listKey, $variants);
        }

        return $key;
    }

    public function getOptimizedImage(string $imagePath, int $width, array $options = []): ?OptimizedImage
    {
        $key = $this->generateOptimizedKey($imagePath, $width, $options);
        if (empty($key)) {
            return null;
        }
        return $this->safeGet($key);
    }

    public function getResponsiveSet(string $imagePath, array $breakpoints): ?ResponsiveImageSet
    {
        $key = $this->generateResponsiveKey($imagePath, $breakpoints);
        return $this->safeGet($key);
    }

    public function deleteImageCache(string $imagePath): int
    {
        try {
            $baseKey = $this->generateBaseKey($imagePath);
            $listKey = $this->getVariantListKey($baseKey);

            $variantKeys = $this->safeGet($listKey, []);
            $deleted = 0;

            foreach ($variantKeys as $variantKey) {
                if ($this->deleteImageVariant($variantKey)) {
                    $deleted++;
                }
            }

            $this->safeDelete($listKey);

            $this->cleanupTagFiles();

            return $deleted;
        } catch (Throwable $e) {
            error_log('Error in deleteImageCache: ' . $e->getMessage());
            return 0;
        }
    }

    public function deleteByPattern(string $pattern): bool
    {
        try {
            // Get all keys from cache
            $allKeys = $this->cache->getKeys('*');
            $deleted = false;

            $pattern = str_replace('*', '.*', $pattern);
            $pattern = '/' . $pattern . '/';

            foreach ($allKeys as $key) {
                if (preg_match($pattern, $key)) {
                    if ($this->deleteImageVariant($key)) {
                        $deleted = true;
                    }
                }
            }

            return $deleted;
        } catch (Throwable $e) {
            error_log('Error in deleteByPattern: ' . $e->getMessage());
            return false;
        }
    }

    public function deleteImageVariant(string $key): bool
    {
        // Sanitize the key first
        $sanitizedKey = $this->sanitizeKey($key);

        // Get the image before deletion to find physical file
        $image = $this->safeGet($sanitizedKey);

        // Delete physical file if it exists
        if ($image instanceof OptimizedImage) {
            $path = $image->getPath();
            if ($this->fileOps->exists($path)) {
                $this->fileOps->delete($path);
            }
        }

        // Delete from cache - this should also remove from tag indexes
        return $this->safeDelete($sanitizedKey);
    }

    public function getStats(): array
    {
        // This method doesn't use keys, so no sanitization needed
        $stats = [
            'cache' => $this->cache->getStats(),
            'physical' => [],
        ];

        // Add physical file stats
        $files = glob($this->physicalPath . '*');
        $imageFiles = array_filter($files, fn ($f) => is_file($f));

        $stats['physical']['files'] = count($imageFiles);
        $stats['physical']['size'] = array_sum(array_map('filesize', $imageFiles));
        $stats['physical']['path'] = $this->physicalPath;

        return $stats;
    }

    public function clear(): bool
    {
        // Delete all physical files
        $files = glob($this->physicalPath . '*');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }

        // Clear cache
        return $this->cache->clear();
    }

    public function cleanupOrphanedTags(): int
    {
        $totalCleaned = 0;
        $cacheDir = $this->cache->getCacheDirectory();

        $tagFiles = glob($cacheDir . '.tag_*');

        foreach ($tagFiles as $tagFile) {
            if (!is_file($tagFile)) {
                continue;
            }

            $filename = basename($tagFile);
            $beforeCount = 0;
            $afterCount = 0;

            try {
                $content = file_get_contents($tagFile);
                if ($content === false) {
                    continue;
                }

                $keys = json_decode($content, true);
                if (!is_array($keys)) {
                    continue;
                }

                $beforeCount = count($keys);
                $validKeys = [];

                foreach ($keys as $key) {
                    if ($this->safeExists($key)) {
                        $validKeys[] = $key;
                    }
                }

                $afterCount = count($validKeys);
                $cleaned = $beforeCount - $afterCount;

                if (empty($validKeys)) {
                    unlink($tagFile);
                    $totalCleaned += $beforeCount;
                    error_log(sprintf('Deleted empty tag file: %s (%d entries removed)', $filename, $beforeCount));
                } elseif ($cleaned > 0) {
                    file_put_contents($tagFile, json_encode(array_values($validKeys), JSON_PRETTY_PRINT));
                    $totalCleaned += $cleaned;
                    error_log(sprintf('Cleaned tag file: %s - removed %d orphaned entries', $filename, $cleaned));
                }
            } catch (Throwable $e) {
                error_log("Error cleaning tag file {$tagFile}: " . $e->getMessage());
            }
        }

        return $totalCleaned;
    }

    public function getCacheDirectory(): string
    {
        return $this->cache->getCacheDirectory();
    }

    public function getPhysicalPath(): string
    {
        return $this->physicalPath;
    }

    private function cleanupTagFiles(): void
    {
        $tagFiles = ['.tag_images', '.tag_optimized', '.tag_responsive_sets'];

        foreach ($tagFiles as $tagFile) {
            $this->cleanupTagFile($tagFile);
        }
    }

    private function cleanupTagFile(string $tagFile): void
    {
        $tagFilePath = $this->cache->getCacheDirectory() . $tagFile . '.json';

        if (!file_exists($tagFilePath)) {
            return;
        }

        try {
            // Read current tag contents
            $content = file_get_contents($tagFilePath);
            if ($content === false) {
                return;
            }

            $keys = json_decode($content, true);
            if (!is_array($keys)) {
                return;
            }

            // Filter out keys that no longer exist
            $validKeys = [];
            $removedCount = 0;

            foreach ($keys as $key) {
                if ($this->safeExists($key)) {
                    $validKeys[] = $key;
                } else {
                    $removedCount++;
                }
            }

            // Log if we removed any keys
            if ($removedCount > 0) {
                error_log(sprintf(
                    'Cleaned %d orphaned references from tag file: %s',
                    $removedCount,
                    basename($tagFilePath),
                ));
            }

            // Write back or delete if empty
            if (empty($validKeys)) {
                unlink($tagFilePath);
                error_log(sprintf('Deleted empty tag file: %s', basename($tagFilePath)));
            } elseif (count($validKeys) !== count($keys)) {
                file_put_contents($tagFilePath, json_encode(array_values($validKeys), JSON_PRETTY_PRINT));
            }
        } catch (Throwable $e) {
            error_log("Error cleaning up tag file {$tagFilePath}: " . $e->getMessage());
        }
    }

    private function sanitizeKey(string $key): string
    {
        $key = preg_replace('/[^a-zA-Z0-9_\.]/', '_', $key);
        $key = preg_replace('/_+/', '_', $key);
        $key = trim($key, '_.');
        if (empty($key)) {
            $key = 'empty_key_' . uniqid();
        }
        if (strlen($key) > 64) {
            $key = substr($key, 0, 64);
        }
        return $key;
    }

    private function safeGet(string $key, mixed $default = null): mixed
    {
        $sanitizedKey = $this->sanitizeKey($key);

        try {
            return $this->cache->get($sanitizedKey) ?? $default;
        } catch (CacheInvalidArgumentException $e) {
            error_log("Cache get failed for key: {$key} (sanitized: {$sanitizedKey}) - " . $e->getMessage());
            return $default;
        }
    }

    private function safeSet(string $key, mixed $value, array $tags = []): bool
    {
        $sanitizedKey = $this->sanitizeKey($key);

        try {
            return $this->cache->setWithTags($sanitizedKey, $value, 604800, $tags);
        } catch (CacheInvalidArgumentException $e) {
            error_log("Cache set failed for key: {$key} (sanitized: {$sanitizedKey}) - " . $e->getMessage());
            return false;
        }
    }

    private function safeDelete(string $key): bool
    {
        $sanitizedKey = $this->sanitizeKey($key);

        try {
            return $this->cache->delete($sanitizedKey);
        } catch (CacheInvalidArgumentException $e) {
            error_log("Cache delete failed for key: {$key} (sanitized: {$sanitizedKey}) - " . $e->getMessage());
            return false;
        }
    }

    private function safeExists(string $key): bool
    {
        $sanitizedKey = $this->sanitizeKey($key);

        try {
            return $this->cache->exists($sanitizedKey);
        } catch (CacheInvalidArgumentException $e) {
            return false;
        }
    }

    private function generateBaseKey(string $imagePath): string
    {
        $metadata = $this->metadataService->createFromWebPath($imagePath) ?? [];
        $absolutePath = $this->metadataService->webPathToAbsolutePath($imagePath);
        $fileInfo = new FileInformation($absolutePath);

        $data = [
            'path' => $fileInfo->getPathname(),
            'modified' => $metadata['modified_at'] ?? $fileInfo->getMTime(),
            'size' => $metadata['size'] ?? $fileInfo->getSize(),
            'hash' => $metadata['hash'] ?? $fileInfo->getHash('md5'),
        ];

        // Sanitize filename first
        $rawFilename = pathinfo($fileInfo->getFilename(), PATHINFO_FILENAME);
        $cleanFilename = preg_replace('/[^a-zA-Z0-9]/', '_', $rawFilename);

        if (strlen($cleanFilename) > 20) {
            $cleanFilename = substr($cleanFilename, 0, 20);
        }

        $hash = md5(serialize($data));
        $key = self::BASE_PREFIX . $hash . '_' . $cleanFilename;

        return $this->sanitizeKey($key);
    }

    private function getVariantListKey(string $baseKey): string
    {
        $sanitizedBase = $this->sanitizeKey($baseKey);
        return $this->sanitizeKey($sanitizedBase . self::VARIANT_LIST_SUFFIX);
    }
}