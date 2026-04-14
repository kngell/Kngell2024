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

    public function deletePattern(string $pattern): bool;

    public function setWithTags(string $key, mixed $value, int|null $ttl = null, array $tags = []): bool;

    public function invalidateTags(array $tags): bool;

    public function getStats(): array;

    public function getKeys(string $pattern = '*'): array;

    public function collectGarbage(): bool;

    public function remember(string $key, callable $callback, ?int $ttl = null): mixed;

    public function rememberForever(string $key, callable $callback): mixed;

    public function rememberWithTags(string $key, callable $callback, ?int $ttl = null, array $tags = []): mixed;

    public function rememberMany(array $keys, callable $callback, ?int $ttl = null): array;

    public function getCacheDirectory(): string;
}