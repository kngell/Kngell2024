<?php

declare(strict_types=1);

class Cache extends AbstractCache
{
    use CacheRememberTrait;

    public function __construct(?string $cacheIdentifier, ?CacheStorageInterface $storage, array $options, private ?SmartSerializerInterface $serializer = null)
    {
        parent::__construct($cacheIdentifier, $storage, $options);
        if ($this->serializer === null) {
            $this->serializer = new SmartSerializer(
                $options['compress'] ?? false,
                $options['use_igbinary'] ?? false,
            );
        }
    }

    public function getCacheDirectory(): string
    {
        return $this->storage->getCacheDirectory();
    }

    public function set(string $key, mixed $value, int|null $ttl = null): bool
    {
        $this->ensureCacheEntryIdentifierIsvalid($key);

        try {
            $serializedValue = $this->serializer->serialize($value);
            $isCompressed = false;
            if ($this->serializer->supportsCompression() && strlen($serializedValue) > 1024) {
                $serializedValue = $this->serializer->compress($serializedValue);
                $isCompressed = true;
            }

            // Prefix with a flag if compressed
            $finalValue = $isCompressed ? ('C:' . $serializedValue) : $serializedValue;

            // Use $finalValue instead of $serializedValue
            $this->storage->setCache($key, $finalValue, $ttl);
        } catch (Throwable $throwable) {
            throw new CacheException(
                'Failed to store cache key: ' . $key,
                0,
                $throwable,
            );
        }

        return true;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $this->ensureCacheEntryIdentifierIsvalid($key);

        try {
            $data = $this->storage->getCache($key);

            if ($data === false) {
                return $default;
            }

            $data = (string) $data;

            if ($this->serializer->supportsCompression() && str_starts_with($data, 'C:')) {
                $compressedData = substr($data, 2);
                try {
                    $data = $this->serializer->decompress($compressedData);
                } catch (SerializationException $e) {
                    // Decompression failed, treat as non-compressed or corrupt
                }
            }

            return $this->serializer->unserialize($data);
        } catch (Throwable $throwable) {
            throw new CacheException(
                'Failed to retrieve cache key: ' . $key,
                0,
                $throwable,
            );
        }
    }

    public function setWithTags(string $key, mixed $value, int|null $ttl = null, array $tags = []): bool
    {
        $result = $this->set($key, $value, $ttl);

        if ($result && !empty($tags) && $this->storage instanceof TaggableCacheStorageInterface) {
            foreach ($tags as $tag) {
                $this->storage->addKeyToTag($key, $tag, $ttl);
            }
        }

        return $result;
    }

    public function delete(string $key): bool
    {
        $this->ensureCacheEntryIdentifierIsvalid($key);
        try {
            $this->storage->removeCache($key);
        } catch (Throwable $throwable) {
            throw new CacheException('An exception was thrown in retrieving the key from the cache backend.', 0, $throwable);
        }
        return true;
    }

    public function deletePattern(string $pattern): bool
    {
        if (method_exists($this->storage, 'deletePattern')) {
            $deleted = $this->storage->deletePattern($pattern);
            return $deleted > 0;
        }

        return false;
    }

    public function invalidateTags(array $tags): bool
    {
        if (!$this->storage instanceof TaggableCacheStorageInterface) {
            return false;
        }

        $totalRemoved = 0;
        foreach ($tags as $tag) {
            $removed = $this->storage->invalidateTag($tag);
            $totalRemoved += $removed;
        }

        return $totalRemoved > 0;
    }

    public function getStats(): array
    {
        if (method_exists($this->storage, 'getStats')) {
            return $this->storage->getStats();
        }

        return [
            'identifier' => $this->cacheIdentifier,
            'storage_class' => get_class($this->storage),
        ];
    }

    /**
     * @inheritDoc
     *
     * @return bool
     */
    public function clear(): bool
    {
        $this->storage->flush();
        return true;
    }

    public function getRemainingTtl(string $key): ?int
    {
        if (method_exists($this->storage, 'getRemainingTtl')) {
            return $this->storage->getRemainingTtl($key);
        }

        return null;
    }

    public function getMultiple(iterable $keys, mixed $default = null): iterable
    {
        $keys = is_array($keys) ? $keys : iterator_to_array($keys);
        $results = [];

        if (method_exists($this->storage, 'getMultiple')) {
            $rawResults = $this->storage->getMultiple($keys, $default);

            foreach ($rawResults as $key => $data) {
                if ($data !== false) {
                    $data = (string) $data;
                    if ($this->serializer->supportsCompression() && str_starts_with($data, 'C:')) {
                        $compressedData = substr($data, 2);
                        try {
                            $data = $this->serializer->decompress($compressedData);
                        } catch (Throwable) {
                        }
                    }

                    try {
                        $results[$key] = $this->serializer->unserialize($data);
                    } catch (Throwable $e) {
                        $results[$key] = $default;
                    }
                } else {
                    $results[$key] = $default;
                }
            }

            return $results;
        }
        foreach ($keys as $key) {
            $results[$key] = $this->get($key, $default);
        }

        return $results;
    }

    public function collectGarbage(): bool
    {
        if (method_exists($this->storage, 'collectGarbage')) {
            $this->storage->collectGarbage();
            return true;
        }

        return false;
    }

    public function getKeys(string $pattern = '*'): array
    {
        if (method_exists($this->storage, 'getKeys')) {
            return $this->storage->getKeys($pattern);
        }

        return [];
    }

    /**
     * @inheritDoc
     *
     * @param iterable $values
     * @param int|null $ttl
     *
     * @throws CacheException
     *
     * @return bool
     */
    public function setMultiple(iterable $values, int|null $ttl = null): bool
    {
        $all = true;
        foreach ($values as $key => $value) {
            $all = $this->set($key, $value, $ttl) && $all;
        }

        return $all;
    }

    public function deleteMultiple(iterable $keys): bool
    {
        if (method_exists($this->storage, 'deleteMultiple')) {
            return $this->storage->deleteMultiple($keys);
        }

        $all = true;
        foreach ($keys as $key) {
            $all = $this->delete($key) && $all;
        }

        return $all;
    }

    /**
     * @inheritdoc
     *
     * @param string $key
     *
     * @return bool
     */
    public function exists(string $key): bool
    {
        $this->ensureCacheEntryIdentifierIsvalid($key);
        return $this->storage->hasCache($key);
    }

    private function isBinary(string $data): bool
    {
        return preg_match('~[^\x20-\x7E\t\r\n]~', $data) > 0;
    }

    private function getCacheFilePath(string $key): string
    {
        // This depends on your storage implementation
        if (method_exists($this->storage, 'cacheEntryPathAndFilename')) {
            return $this->storage->cacheEntryPathAndFilename($key);
        }
        return 'unknown';
    }

    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);

        return round($bytes, $precision) . ' ' . $units[$pow];
    }

    /**
     * Convert wildcard pattern to regex.
     */
    private function patternToRegex(string $pattern): string
    {
        $pattern = preg_quote($pattern, '/');
        $pattern = str_replace('\*', '.*', $pattern);
        $pattern = str_replace('\?', '.', $pattern);

        return '/^' . $pattern . '$/';
    }
}