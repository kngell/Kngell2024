<?php

declare(strict_types=1);

abstract class ConfigExporterService
{
    protected const CACHE_DURATION_DEVELOPMENT = 300;
    protected const CACHE_DURATION_PRODUCTION = 3600;

    protected string $configType = 'generic';

    public function __construct(
        protected CacheInterface $cache,
    ) {
    }

    abstract public function exportForClient(string $source, array $options = []): array;

    /**
     * Clear cache for a source.
     */
    public function clearCache(string $source): bool
    {
        $cacheKey = $this->generateValidCacheKey($source, []);
        return $this->cache->delete($cacheKey);
    }

    /**
     * Get cached or generate fresh data.
     */
    protected function getCachedOrGenerate(string $source, callable $generator, array $options = []): array
    {
        $cacheKey = $this->generateValidCacheKey($source, $options);

        // Try cache
        if ($cached = $this->cache->get($cacheKey)) {
            if ($this->isCacheValid($cached, $source)) {
                return $cached;
            }
        }

        // Generate fresh
        $data = $generator();

        // Add metadata
        $data = $this->addMetadata($data, $source);

        // Cache it
        $duration = $this->getCacheDuration();
        $this->cache->set($cacheKey, $data, $duration);

        return $data;
    }

    /**
     * Generate VALID cache key (matching your pattern).
     */
    private function generateValidCacheKey(string $source, array $options): string
    {
        // Create base key
        $base = 'export_' . $this->configType;

        // Add source identifier (simplified)
        $sourceId = $this->getSourceIdentifier($source);

        // Add options hash if any
        $optionsHash = '';
        if (!empty($options)) {
            $optionsHash = '_' . substr(md5(json_encode($options)), 0, 8);
        }

        // Add environment
        $env = preg_replace('/[^a-zA-Z0-9]/', '_', Environment::get('APP_ENV', 'prod'));

        // Build key
        $key = $base . '_' . $sourceId . $optionsHash . '_' . $env;

        // Ensure it matches pattern: /^[a-zA-Z0-9_\.]{1,64}$/
        $key = preg_replace('/[^a-zA-Z0-9_\.]/', '_', $key);

        // Truncate to 64 chars
        if (strlen($key) > 64) {
            $key = substr($key, 0, 64);
        }

        return $key;
    }

    /**
     * Get source identifier (for cache key).
     */
    private function getSourceIdentifier(string $source): string
    {
        // If it's a file path, use basename
        if (str_contains($source, '/') || str_contains($source, '\\')) {
            $sourceId = basename($source);
            // Remove extension
            $sourceId = pathinfo($sourceId, PATHINFO_FILENAME);
        } else {
            $sourceId = $source;
        }

        // Clean up
        $sourceId = preg_replace('/[^a-zA-Z0-9]/', '_', $sourceId);

        // Make sure it's not empty
        if (empty($sourceId)) {
            $sourceId = 'default';
        }

        return $sourceId;
    }

    private function isCacheValid(array $cachedData, string $source): bool
    {
        // In dev debug mode, always refresh
        if (Environment::isDevelopment() && Environment::isDebug()) {
            return false;
        }

        // Check if source file changed (if it's a file)
        if (file_exists($source)) {
            $currentMTime = filemtime($source);
            $cachedMTime = $cachedData['_metadata']['source_mtime'] ?? 0;

            return $currentMTime <= $cachedMTime;
        }

        return true;
    }

    private function addMetadata(array $data, string $source): array
    {
        $metadata = [
            'timestamp' => time(),
            'environment' => Environment::get('APP_ENV', 'unknown'),
            'config_type' => $this->configType,
            'source' => $source,
        ];

        if (file_exists($source)) {
            $metadata['source_file'] = $source;
            $metadata['source_mtime'] = filemtime($source);
        }

        return array_merge($data, ['_metadata' => $metadata]);
    }

    private function getCacheDuration(): int
    {
        return Environment::isProduction()
            ? self::CACHE_DURATION_PRODUCTION
            : self::CACHE_DURATION_DEVELOPMENT;
    }
}