<?php

declare(strict_types=1);
interface CacheableSectionServiceInterface extends SectionServiceInterface
{
    public function clearAllCaches(): bool;

    public function getCacheStats(): array;

    public function warmupCache(array $identifiers): int;
}