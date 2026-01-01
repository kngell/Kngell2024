<?php

declare(strict_types=1);

interface TaggableCacheStorageInterface
{
    public function addKeyToTag(string $key, string $tag, ?int $ttl): bool;

    public function invalidateTag(string $tag): int;
}