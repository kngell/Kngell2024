<?php

declare(strict_types=1);

interface SectionServiceInterface
{
    public function getForPage(?string $page = null): DTOResponseInterface|array;

    public function getDefaultResponse(): DTOResponseInterface|array;

    public function clearAllCaches(): bool;

    public function getCacheStats(): array;
}