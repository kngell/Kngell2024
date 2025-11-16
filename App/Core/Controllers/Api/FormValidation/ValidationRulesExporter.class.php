<?php

declare(strict_types=1);

use Symfony\Component\Yaml\Yaml;

class ValidationRulesExporter
{
    private const array CLIENT_ALLOWED_RULES = [
        'required', 'min', 'max', 'pattern', 'numeric',
        'min_value', 'max_value', 'required_if', 'lte', 'gte',
        'array', 'max_items', 'min_items',
        // File validation rules
        'mimes', 'file_size', 'upload_limit', 'post_limit', 'max_files',
        // Additional rules you might need
        'integer', 'decimal', 'boolean', 'email', 'url', 'date', 'time',
    ];

    private const array SERVER_ONLY_RULES = [
        'unique', 'exists', 'file', 'image', 'custom_callback',
    ];

    // Different cache durations for different environments
    private const CACHE_DURATION_DEVELOPMENT = 300;  // 5 minutes
    private const CACHE_DURATION_PRODUCTION = 3600;  // 1 hour
    private const CACHE_KEY_PREFIX = 'validation_rules_';

    public function __construct(
        private ValidationMessageService $messageService,
        private CacheInterface $cache,
    ) {
    }

    public function exportForClient(string $rulesFilePath, ?string $outputPath = null): array
    {
        $cacheKey = $this->generateCacheKey($rulesFilePath);

        // Try to get from cache first
        $cachedData = $this->cache->get($cacheKey);
        if ($cachedData !== null && $this->isCacheFresh($cachedData, $rulesFilePath)) {
            return $cachedData;
        }

        // Export fresh data
        $rules = Yaml::parseFile($rulesFilePath);
        $clientData = [
            'rules' => $this->filterClientRules($rules),
            'settings' => $this->getGlobalSettings(),
            'timestamp' => time(),
            'source_file' => $rulesFilePath,
            'source_mtime' => filemtime($rulesFilePath),
            'environment' => Environment::get('APP_ENV', 'unknown'),
        ];

        // Cache with environment-appropriate duration
        $cacheDuration = Environment::isProduction()
            ? self::CACHE_DURATION_PRODUCTION
            : self::CACHE_DURATION_DEVELOPMENT;

        $this->cache->set($cacheKey, $clientData, $cacheDuration);

        // Only write to output file in production (for build process)
        if ($outputPath && Environment::isProduction()) {
            $jsonData = json_encode($clientData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            file_put_contents($outputPath, $jsonData);
        }

        return $clientData;
    }

    public function getGlobalSettings(): array
    {
        $cacheKey = self::CACHE_KEY_PREFIX . 'global_settings';

        // Try cache first
        $cachedSettings = $this->cache->get($cacheKey);
        if ($cachedSettings !== null) {
            return $cachedSettings;
        }

        $settings = [
            'messages' => $this->messageService->getAllMessages(),
            'classes' => [
                'hint' => $this->messageService->getHintClasses(),
                'error' => $this->messageService->getErrorClasses(),
            ],
            'timestamp' => time(),
            'environment' => Environment::get('APP_ENV', 'unknown'),
        ];

        // Cache global settings with environment-appropriate duration
        $cacheDuration = Environment::isProduction()
            ? self::CACHE_DURATION_PRODUCTION * 24 // 24 hours in production
            : self::CACHE_DURATION_DEVELOPMENT;     // 5 minutes in development

        $this->cache->set($cacheKey, $settings, $cacheDuration);

        return $settings;
    }

    public function clearCache(?string $rulesFilePath = null): bool
    {
        if ($rulesFilePath) {
            // Clear specific rules cache
            $cacheKey = $this->generateCacheKey($rulesFilePath);
            return $this->cache->delete($cacheKey);
        } else {
            // Clear all validation caches
            $this->cache->delete(self::CACHE_KEY_PREFIX . 'global_settings');
            // Note: You might want to implement pattern-based deletion if your cache supports it
            return true;
        }
    }

    public function getCacheStats(?string $rulesFilePath = null): array
    {
        $cacheKey = $rulesFilePath ? $this->generateCacheKey($rulesFilePath) : self::CACHE_KEY_PREFIX . 'global_settings';
        $cachedData = $this->cache->get($cacheKey);

        return [
            'is_cached' => $cachedData !== null,
            'cache_key' => $cacheKey,
            'timestamp' => $cachedData['timestamp'] ?? null,
            'source_file' => $cachedData['source_file'] ?? null,
            'source_mtime' => $cachedData['source_mtime'] ?? null,
        ];
    }

    private function isCacheFresh(?array $cachedData, string $rulesFilePath): bool
    {
        if ($cachedData === null || !file_exists($rulesFilePath)) {
            return false;
        }

        // In development, be more aggressive about cache invalidation
        if (Environment::isDevelopment() && Environment::isDebug()) {
            return false; // Always refresh in debug development mode
        }

        // Check if source file has been modified since cache was created
        $currentFileMTime = filemtime($rulesFilePath);
        $cachedFileMTime = $cachedData['source_mtime'] ?? 0;

        return $currentFileMTime <= $cachedFileMTime;
    }

    private function generateCacheKey(string $rulesFilePath): string
    {
        $fileMTime = file_exists($rulesFilePath) ? filemtime($rulesFilePath) : 0;
        $envSuffix = Environment::get('APP_ENV', 'unknown');
        return self::CACHE_KEY_PREFIX . md5($rulesFilePath . $fileMTime) . '_' . $envSuffix;
    }

    private function filterClientRules(array $rules): array
    {
        $clientRules = [];

        foreach ($rules as $field => $fieldRules) {
            // Handle nested array structures (like variations with items)
            if (isset($fieldRules['items']) && is_array($fieldRules['items'])) {
                $filteredRules = $this->filterNestedStructure($fieldRules);
            } else {
                $filteredRules = $this->filterFlatFieldRules($fieldRules);
            }

            if (!empty($filteredRules)) {
                $clientRules[$field] = $filteredRules;
            }
        }

        return $clientRules;
    }

    private function filterFlatFieldRules(array $fieldRules): array
    {
        $filteredRules = [];

        foreach ($fieldRules as $rule => $value) {
            // Skip server-only rules
            if (in_array($rule, self::SERVER_ONLY_RULES, true)) {
                continue;
            }

            // Only include client-allowed rules
            if (in_array($rule, self::CLIENT_ALLOWED_RULES, true)) {
                $filteredRules[$rule] = $value;
            }

            // Always include display name
            if ($rule === 'display') {
                $filteredRules[$rule] = $value;
            }
        }

        return $filteredRules;
    }

    private function filterNestedStructure(array $structure): array
    {
        $filtered = [];

        // Always include array structure rules
        $allowedStructureRules = ['array', 'max_items', 'min_items', 'items', 'type'];

        foreach ($structure as $key => $value) {
            if (in_array($key, $allowedStructureRules, true)) {
                // Recursively filter nested items
                if ($key === 'items' && is_array($value)) {
                    $filtered[$key] = $this->filterNestedItems($value);
                } else {
                    $filtered[$key] = $value;
                }
            }

            // Always include display for the main array field
            if ($key === 'display') {
                $filtered[$key] = $value;
            }
        }

        return $filtered;
    }

    private function filterNestedItems(array $items): array
    {
        $filtered = [];

        foreach ($items as $key => $value) {
            if ($key === 'type') {
                $filtered[$key] = $value;
            } elseif ($key === 'rules' && is_array($value)) {
                // Recursively filter the nested rules
                $filtered[$key] = $this->filterClientRules($value);
            } else {
                $filtered[$key] = $value;
            }
        }

        return $filtered;
    }
}