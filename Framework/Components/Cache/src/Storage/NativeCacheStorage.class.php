<?php

declare(strict_types=1);

class NativeCacheStorage extends AbstractCacheStorage implements TaggableCacheStorageInterface
{
    private const TAG_FILE_PREFIX = '.tag_';
    private const TAG_FILE_EXTENSION = '.json';

    public function __construct(
        CacheEnvironmentConfigurations $envConfigurations,
        array $options,
        DirectoryManager $directoryManager,
        FileContentManager $fileContentManager,
    ) {
        parent::__construct($envConfigurations, $options, $directoryManager, $fileContentManager);
        $this->ensureCacheDirectory();
        $this->fixCacheDirectoryPath();
    }

    public function setCache(string $key, string $value, ?int $ttl = null): void
    {
        $this->isCacheValidated($key);
        $cacheFilePath = $this->cacheEntryPathAndFilename($key);

        // Check if it's currently a directory (from previous bug)
        if ($this->directoryManager->exists($cacheFilePath) && !is_file($cacheFilePath)) {
            $this->cleanupDirectoryInsteadOfFile($cacheFilePath);
        }
        try {
            $this->fileContentManager->write($cacheFilePath, $value);
        } catch (FileException $e) {
            throw new CacheException(
                'The cache file "' . $cacheFilePath . '" could not be written: ' . $e->getMessage(),
                0,
                $e,
            );
        }

        // Write TTL metadata
        $this->writeTtlMetadata($key, $ttl);
    }

    public function getCache(string $key): string|false
    {
        $this->isCacheValidated($key);
        $cacheFilePath = $this->cacheEntryPathAndFilename($key);
        if (!file_exists($cacheFilePath) || !is_file($cacheFilePath)) {
            return false;
        }

        if ($this->isCacheExpired($key)) {
            $this->removeCache($key);
            return false;
        }

        try {
            return $this->fileContentManager->read($cacheFilePath);
        } catch (FileException) {
            return false;
        }
    }

    public function getMultiple(iterable $keys, mixed $default = null): iterable
    {
        $results = [];
        foreach ($keys as $key) {
            $this->isCacheValidated($key, false);

            $data = $this->getCache($key);
            $results[$key] = $data;
        }

        return $results;
    }

    public function deleteMultiple(iterable $keys): bool
    {
        $allSucceeded = true;
        foreach ($keys as $key) {
            if (!$this->removeCache($key)) {
                $allSucceeded = false;
            }
        }

        return $allSucceeded;
    }

    public function addKeyToTag(string $key, string $tag, ?int $ttl): bool
    {
        $tagFile = $this->getTagFilePath($tag);
        $keys = $this->readTagIndex($tag);

        // Ensure the key is only added once
        if (!in_array($key, $keys, true)) {
            $keys[] = $key;
        }

        // Write the updated keys list back to the file
        try {
            $content = json_encode($keys, JSON_PRETTY_PRINT);
            $this->fileContentManager->write($tagFile, $content);
            return true;
        } catch (FileException) {
            return false;
        }
    }

    public function invalidateTag(string $tag): int
    {
        $keys = $this->readTagIndex($tag);
        $removedCount = 0;

        foreach ($keys as $key) {
            if ($this->removeCache($key)) {
                $removedCount++;
            }
        }

        $tagFile = $this->getTagFilePath($tag);
        if (file_exists($tagFile)) {
            unlink($tagFile);
        }

        return $removedCount;
    }

    // public function invalidateTag(string $tag): int
    // {
    //     $keys = $this->readTagIndex($tag);
    //     $removedCount = 0;

    //     foreach ($keys as $key) {
    //         // Using removeCache ensures both the cache entry and its TTL file are deleted
    //         if ($this->removeCache($key)) {
    //             $removedCount++;
    //         }
    //     }

    //     // After removing all keys, delete the tag index file itself
    //     $tagFile = $this->getTagFilePath($tag);
    //     if (file_exists($tagFile)) {
    //         unlink($tagFile);
    //     }

    //     return $removedCount;
    // }

    public function removeCache(string $key): bool
    {
        $cacheFilePath = $this->cacheEntryPathAndFilename($key);
        $ttlFilePath = $this->getTtlMetadataPath($key);
        $cacheRemoved = true;

        if ($this->directoryManager->exists($cacheFilePath) && is_dir($cacheFilePath)) {
            return $this->cleanupDirectoryInsteadOfFile($cacheFilePath);
        }

        if (file_exists($cacheFilePath)) {
            $cacheRemoved = unlink($cacheFilePath);
        }

        $ttlRemoved = true;
        if (file_exists($ttlFilePath)) {
            $ttlRemoved = unlink($ttlFilePath);
        }
        return $cacheRemoved && $ttlRemoved;
    }

    public function hasCache(string $key): bool
    {
        $this->isCacheValidated($key, false);

        // First check if file exists
        $cacheFile = $this->cacheEntryPathAndFilename($key);
        if (!file_exists($cacheFile)) {
            return false;
        }

        // Check expiration
        if ($this->isCacheExpired($key)) {
            // Auto-clean expired file
            $this->removeCache($key);
            return false;
        }

        return true;
    }

    public function flush(): void
    {
        try {
            $this->directoryManager->delete($this->cacheDirectory);
            // Recreate empty directory
            $this->directoryManager->create($this->cacheDirectory);
        } catch (FileException $e) {
            throw new CacheException('Failed to flush cache: ' . $e->getMessage(), 0, $e);
        }
    }

    public function collectGarbage(): void
    {
        $files = $this->directoryManager->list($this->cacheDirectory);

        foreach ($files as $file) {
            if ($file->isFile() && str_ends_with($file->getPathname(), $this->cacheEntryFileExtension)) {
                $key = $this->extractKeyFromFilename(basename($file->getPathname()));

                // Check if expired using the same logic
                if ($this->isCacheExpired($key)) {
                    $this->removeCache($key);
                }
            }
        }
    }

    public function getKeys(string $pattern = '*'): array
    {
        $files = $this->directoryManager->list($this->cacheDirectory);
        $keys = [];

        foreach ($files as $file) {
            if ($file->isFile() && str_ends_with($file->getPathname(), $this->cacheEntryFileExtension)) {
                $filename = basename($file->getPathname(), $this->cacheEntryFileExtension);
                if ($this->matchesPattern($filename, $pattern)) {
                    $keys[] = $filename;
                }
            }
        }

        return $keys;
    }

    public function deletePattern(string $pattern): int
    {
        $keys = $this->getKeys($pattern);
        $deleted = 0;

        foreach ($keys as $key) {
            if ($this->removeCache($key)) {
                $deleted++;
            }
        }

        return $deleted;
    }

    public function getRemainingTtl(string $key): ?int
    {
        $data = $this->readTtlMetadata($key);

        if ($data === null) {
            return null;
        }

        if (isset($data['expires_at'])) {
            $remaining = $data['expires_at'] - time();
            return max(0, $remaining);
        }

        return null;
    }

    public function getStats(): array
    {
        $sizeInBytes = $this->directoryManager->getSize($this->cacheDirectory);
        return [
            'directory' => $this->cacheDirectory,
            'total_files' => $this->directoryManager->getFileCount($this->cacheDirectory),
            'total_size_human' => ByteHelper::format($sizeInBytes),
            'total_size_bytes' => $sizeInBytes,
            'total_directories' => $this->directoryManager->getDirectoryCount($this->cacheDirectory),
            'is_readable' => $this->directoryManager->isReadable($this->cacheDirectory),
            'is_writable' => $this->directoryManager->isWritable($this->cacheDirectory),
            'permissions' => $this->directoryManager->getPermissions($this->cacheDirectory),
            'iterator_valid' => $this->valid(),
        ];
    }

    public function current(): bool|string
    {
        // Use parent's iterator but ensure it's a file
        $value = parent::current();

        if ($value === false) {
            return false;
        }

        // Additional validation if needed
        $currentKey = $this->key();
        $currentPath = $this->cacheDirectory . $currentKey . $this->cacheEntryFileExtension;

        if (is_dir($currentPath)) {
            // Skip directories
            $this->next();
            return $this->current();
        }

        return $value;
    }

    private function readTagIndex(string $tag): array
    {
        $tagFile = $this->getTagFilePath($tag);

        if (!file_exists($tagFile)) {
            return [];
        }

        try {
            $content = $this->fileContentManager->read($tagFile);
            if ($content === false) {
                return [];
            }
            $keys = json_decode($content, true);

            return is_array($keys) ? $keys : [];
        } catch (Throwable) {
            // Handle file or JSON decoding errors gracefully
            return [];
        }
    }

    private function getTagFilePath(string $tag): string
    {
        return $this->cacheDirectory . self::TAG_FILE_PREFIX . $tag . self::TAG_FILE_EXTENSION;
    }

    private function ensureCacheDirectory(): void
    {
        if (!$this->directoryManager->exists($this->cacheDirectory)) {
            $this->directoryManager->create($this->cacheDirectory, 0775);
        }
    }

    private function fixCacheDirectoryPath(): void
    {
        // Ensure the cache directory ends with DS
        $this->cacheDirectory = rtrim($this->cacheDirectory, DS) . DS;

        // Fix: Remove any duplicate path segments
        $this->cacheDirectory = preg_replace('#(/+)#', '/', $this->cacheDirectory);
    }

    private function cleanupDirectoryInsteadOfFile(string $path): bool
    {
        try {
            $this->directoryManager->delete($path);

            return true;
        } catch (FileException $e) {
            return false;
        }
    }

    private function extractKeyFromFilename(string $filename): string
    {
        return basename($filename, $this->cacheEntryFileExtension);
    }

    private function matchesPattern(string $key, string $pattern): bool
    {
        if ($pattern === '*') {
            return true;
        }

        // Simple glob pattern matching
        $regex = str_replace(
            ['*', '?', '.'],
            ['.*', '.', '\.'],
            $pattern,
        );

        return preg_match('/^' . $regex . '$/', $key) === 1;
    }

    private function getTtlMetadataPath(string $key): string
    {
        return $this->cacheDirectory . '.ttl_' . $key . '.json';
    }

    private function isCacheExpired(string $key): bool
    {
        $metadata = $this->readTtlMetadata($key);

        if ($metadata === null) {
            return false;
        }

        $expiresAt = $metadata['expires_at'] ?? 0;
        if ($expiresAt === 0) {
            return false;
        }

        return time() > $expiresAt;
    }

    private function readTtlMetadata(string $key): ?array
    {
        $metadataFile = $this->getTtlMetadataPath($key);

        if (!file_exists($metadataFile)) {
            return null;
        }

        $content = $this->readCacheFile($metadataFile);
        if ($content === false) {
            return null;
        }

        try {
            $metadata = json_decode($content, true);
            return is_array($metadata) ? $metadata : null;
        } catch (JsonException) {
            return null;
        }
    }

    private function writeTtlMetadata(string $key, ?int $ttl): void
    {
        $ttlFile = $this->getTtlMetadataPath($key);

        if ($ttl === null) {
            // No TTL, remove metadata if exists
            if (file_exists($ttlFile)) {
                unlink($ttlFile);
            }
            return;
        }

        $data = [
            'key' => $key,
            'created_at' => time(),
            'expires_at' => time() + $ttl,
            'ttl' => $ttl,
        ];

        try {
            $this->fileContentManager->write($ttlFile, json_encode($data, JSON_PRETTY_PRINT));
        } catch (FileException) {
            // Silently fail on TTL metadata - cache still works
        }
    }

    private function isExpired(string $key): bool
    {
        $ttl = $this->getRemainingTtl($key);
        return $ttl !== null && $ttl <= 0;
    }
}