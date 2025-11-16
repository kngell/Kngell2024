<?php

declare(strict_types=1);
class HeaderRegionContext implements RegionContextResolutionInterface
{
    public function __construct(private Request $request)
    {
    }

    public function resolveRegion(): ?string
    {
        $region = $this->request->getHeaders()->get('X-Region');
        return $region && is_string($region) ? strtoupper(trim($region)) : null;
    }

    public function getPriority(): int
    {
        return 90;
    }
}