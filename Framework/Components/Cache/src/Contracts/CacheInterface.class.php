<?php

declare(strict_types=1);

interface CacheInterface
{
    // Existing methods
    public function set(string $key, mixed $value, int|null $ttl = null): bool;

    public function get(string $key, mixed $default = null): mixed;

    public function delete(string $key): bool;

    public function clear(): bool;

    public function getMultiple(iterable $keys, mixed $default = null): iterable;

    public function setMultiple(iterable $values, int|null $ttl = null): bool;

    public function deleteMultiple(iterable $keys): bool;

    public function exists(string $key): bool;

    // NEW METHODS NEEDED
    public function deletePattern(string $pattern): bool;

    public function setWithTags(string $key, mixed $value, int|null $ttl = null, array $tags = []): bool;

    public function invalidateTags(array $tags): bool;

    public function getStats(): array;

    public function getKeys(string $pattern = '*'): array;

    public function collectGarbage(): bool;

    /**
     * Get an item from the cache, or execute the given callback and store the result.
     *
     * @param string $key The cache key
     * @param callable $callback The callback to execute if cache doesn't exist
     * @param int|null $ttl Time to live in seconds, null for forever
     *
     * @return mixed The cached value or the result of the callback
     */
    public function remember(string $key, callable $callback, ?int $ttl = null): mixed;

    /**
     * Get an item from the cache, or execute the given callback and store the result forever.
     *
     * @param string $key The cache key
     * @param callable $callback The callback to execute if cache doesn't exist
     *
     * @return mixed The cached value or the result of the callback
     */
    public function rememberForever(string $key, callable $callback): mixed;

    public function rememberWithTags(string $key, callable $callback, ?int $ttl = null, array $tags = []): mixed;

    public function rememberMany(array $keys, callable $callback, ?int $ttl = null): array;
}