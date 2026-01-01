<?php

declare(strict_types=1);

final class EntityCacheKeyGenerator implements EntityCacheKeyGeneratorInterface
{
    private const int MAX_KEY_LENGTH = 64;
    private const string ALLOWED_CHARS = '/[^a-zA-Z0-9_\.]/';

    public function __construct(
        private readonly TypeNormalizerInterface $normalizer,
    ) {
    }

    public function getCacheIdentifier(Entity $entity): string
    {
        return $this->extractEntityIdentifier($entity);
    }

    public function getCacheKey(Entity $entity): string
    {
        $className = $this->normalizeClassName(get_class($entity));
        $identifier = $this->getCacheIdentifier($entity);

        return $this->buildCacheKey($className, $identifier);
    }

    public function getCacheKeyWithType(Entity $entity): string
    {
        $className = $this->normalizeClassName(get_class($entity));
        $identifier = $this->getTypedCacheIdentifier($entity);
        $safeIdentifier = $this->sanitizeForCache($identifier);

        return "entity_{$className}_{$safeIdentifier}";
    }

    public function getCacheKeyFromIdentifier(string $entityClass, string $identifier): string
    {
        $className = $this->normalizeClassName($entityClass);
        return $this->buildCacheKey($className, $identifier);
    }

    public function extractIdentifierFromKey(string $cacheKey, string $entityClass): ?string
    {
        $className = $this->normalizeClassName($entityClass);
        $prefix = "entity.{$className}.";

        if (!str_starts_with($cacheKey, $prefix)) {
            return null;
        }

        $typedIdentifier = substr($cacheKey, strlen($prefix));

        // Check if it has type prefix
        if (preg_match('/^(uuid|int|str|content)\.(.*)$/', $typedIdentifier, $matches)) {
            $type = $matches[1];
            $id = $matches[2];

            // For UUIDs, we need to add hyphens back
            if ($type === 'uuid' && strlen($id) === 32) {
                return $this->formatUuidWithHyphens($id);
            }

            return $id;
        }

        return $typedIdentifier;
    }

    public function isKeyForEntity(string $cacheKey, string $entityClass): bool
    {
        $className = $this->normalizeClassName($entityClass);
        return str_starts_with($cacheKey, "entity_{$className}_");
    }

    public function normalizeClassName(string $className): string
    {
        // Replace backslashes with dots (dots are allowed)
        $normalized = str_replace('\\', '.', $className);

        // Ensure it matches pattern
        return preg_replace(self::ALLOWED_CHARS, '', $normalized);
    }

    // =================== PRIVATE METHODS ===================

    private function extractEntityIdentifier(Entity $entity): string
    {
        $keyProperty = $entity->getEntityKeyProperty();
        if ($keyProperty !== false) {
            try {
                $value = $entity->getFieldValue($keyProperty);
                if ($value !== null) {
                    $keyProperty = StringUtils::camelCaseToSnakeCase($keyProperty);
                    return (string) $this->normalizer->normalizeValueForDatabase($keyProperty, $value, $entity);
                }
            } catch (Throwable) {
            }
        }
        return $this->createEntityContentHash($entity);
    }

    /**
     * Get typed identifier (with type prefix).
     */
    private function getTypedCacheIdentifier(Entity $entity): string
    {
        $value = $this->extractEntityIdentifier($entity);  // FIXED: Call extractEntityIdentifier, not getCacheIdentifier

        // Early return if already typed
        if (str_starts_with($value, 'uuid_') ||
            str_starts_with($value, 'int_') ||
            str_starts_with($value, 'str_') ||
            str_starts_with($value, 'content_')) {
            return $value;
        }

        // Determine type
        if ($this->isUuidString($value)) {
            return 'uuid_' . $value;
        }

        if ($this->isIntegerString($value)) {
            return 'int_' . $value;
        }

        if (is_string($value)) {
            return 'str_' . $value;
        }

        return 'content_' . $value;
    }

    private function createEntityContentHash(Entity $entity): string
    {
        $data = $entity->toArray();
        $filtered = [];

        foreach ($data as $key => $value) {
            if ($value === null) {
                continue;
            }

            try {
                $normalized = $this->normalizer->normalizeValueForDatabase($key, $value, $entity);
                $filtered[$key] = $normalized;
            } catch (Throwable) {
                // Skip values that can't be normalized
                continue;
            }
        }

        ksort($filtered);
        $hash = md5(json_encode($filtered, JSON_THROW_ON_ERROR));

        return 'content_' . $hash;
    }

    private function formatUuidWithHyphens(string $uuid): string
    {
        if (strlen($uuid) === 32) {
            return sprintf(
                '%s-%s-%s-%s-%s',
                substr($uuid, 0, 8),
                substr($uuid, 8, 4),
                substr($uuid, 12, 4),
                substr($uuid, 16, 4),
                substr($uuid, 20, 12),
            );
        }
        return $uuid;
    }

    private function typeIdentifier(string $identifier): string
    {
        // Check if already has type prefix
        if (preg_match('/^(uuid|int|str|content)\./', $identifier)) {
            return $identifier;
        }

        // Determine type - use dots instead of underscores for type separator
        if ($this->isUuidString($identifier)) {
            // Remove hyphens from UUID (not allowed in pattern)
            $cleanUuid = str_replace('-', '', $identifier);
            return 'uuid.' . $cleanUuid;
        }

        if ($this->isIntegerString($identifier)) {
            return 'int.' . $identifier;
        }

        if (is_string($identifier)) {
            // Clean string for cache
            $cleanString = preg_replace(self::ALLOWED_CHARS, '', $identifier);
            return 'str.' . $cleanString;
        }

        // Fallback to content hash (truncated)
        $hash = md5((string) $identifier);
        return 'content.' . substr($hash, 0, 16);
    }

    private function truncateKey(string $key): string
    {
        if (strlen($key) <= self::MAX_KEY_LENGTH) {
            return $key;
        }

        // Try to truncate from the identifier part first
        $parts = explode('.', $key, 3);

        if (count($parts) === 3) {
            // Format: entity.className.identifier
            $prefix = $parts[0] . '.' . $parts[1] . '.';
            $identifier = $parts[2];

            $availableLength = self::MAX_KEY_LENGTH - strlen($prefix);

            if ($availableLength > 10) { // Need minimum identifier length
                return $prefix . substr($identifier, 0, $availableLength);
            }
        }

        // Fallback: simple truncation
        return substr($key, 0, self::MAX_KEY_LENGTH);
    }

    private function sanitizeForKeyPattern(string $key): string
    {
        // Replace hyphens with dots (dots are allowed)
        $sanitized = str_replace('-', '.', $key);

        // Remove any characters not in [a-zA-Z0-9_.]
        $sanitized = preg_replace(self::ALLOWED_CHARS, '', $sanitized);

        // Remove consecutive dots
        $sanitized = preg_replace('/\.+/', '.', $sanitized);

        // Trim dots from start/end
        return trim($sanitized, '.');
    }

    private function buildCacheKey(string $className, string $identifier): string
    {
        // Type the identifier
        $typedIdentifier = $this->typeIdentifier($identifier);

        // Build the key parts
        $key = "entity.{$className}.{$typedIdentifier}";

        // Ensure it matches the cache pattern
        $key = $this->sanitizeForKeyPattern($key);

        // Ensure max length of 64
        if (strlen($key) > self::MAX_KEY_LENGTH) {
            $key = $this->truncateKey($key);
        }

        return $key;
    }

    private function sanitizeForCache(string $keyPart): string
    {
        // Convert to lowercase
        $sanitized = strtolower($keyPart);

        // Remove ALL non-alphanumeric characters (no underscores, no hyphens)
        return preg_replace('/[^a-z0-9]/', '', $sanitized);
    }

    private function isUuidString(string $value): bool
    {
        // Check with or without hyphens
        $patternWithHyphens = '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i';
        $patternWithoutHyphens = '/^[0-9a-f]{32}$/i';

        return preg_match($patternWithHyphens, $value) || preg_match($patternWithoutHyphens, $value);
    }

    private function isIntegerString(string $value): bool
    {
        return is_numeric($value) && (string) (int) $value === $value;
    }
}