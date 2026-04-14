<?php

declare(strict_types=1);

interface CacheStorageInterface
{
    public function setCache(string $key, string $value, ?int $ttl = null): void;

    public function getCache(string $key): mixed;

    public function hasCache(string $key): bool;

    public function removeCache(string $key): bool;

    public function flush(): void;

    public function collectGarbage(): void;

    public function deletePattern(string $pattern): int;

    public function getStats(): array;

    public function getRemainingTtl(string $key): ?int;

    public function getKeys(string $pattern = '*'): array;

    public function cacheEntryPathAndFilename(string $entryIdentifier): string;

    public function getMultiple(iterable $keys, mixed $default = null): iterable;

    public function deleteMultiple(iterable $keys): bool;

    public function getCacheDirectory(): string;

    // public function addKeyToTag(string $key, string $tag, ?int $ttl);

    // public function invalidateTag(string $tag);
}