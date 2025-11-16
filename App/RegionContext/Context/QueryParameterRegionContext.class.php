<?php

declare(strict_types=1);
class QueryParameterRegionContext implements RegionContextResolutionInterface
{
    public function __construct(private Request $request)
    {
    }

    public function resolveRegion(): ?string
    {
        $region = $this->request->getQuery()->get('region') ?? $this->request->getPost()->get('region');
        return $region && is_string($region) ? strtoupper(trim($region)) : null;
    }

    public function getPriority(): int
    {
        return 100;
    }
}