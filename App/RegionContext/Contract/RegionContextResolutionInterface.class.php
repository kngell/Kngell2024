<?php

declare(strict_types=1);
interface RegionContextResolutionInterface
{
    public function resolveRegion(): ?string;

    public function getPriority(): int;
}