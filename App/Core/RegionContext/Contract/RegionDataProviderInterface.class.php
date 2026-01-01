<?php

declare(strict_types=1);

interface RegionDataProviderInterface
{
    public function getRegionData(string $regionCode): ?Region;

    public function isValidRegion(string $regionCode): bool;

    public function getRegionByCode(string $regionCode): ?Region;

    public function getDefaultRegion(): string;
}