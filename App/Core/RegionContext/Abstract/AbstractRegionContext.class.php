<?php

declare(strict_types=1);

abstract class AbstractRegionContext implements RegionContextResolutionInterface
{
    use RegionContextTrait;

    abstract public function resolveRegion(): ?string;

    abstract public function getPriority(): int;

    // Common validation method
    protected function validateRegionCode(?string $regionCode): ?string
    {
        if ($regionCode === null || $regionCode === '') {
            return null;
        }

        return strtoupper(trim($regionCode));
    }
}