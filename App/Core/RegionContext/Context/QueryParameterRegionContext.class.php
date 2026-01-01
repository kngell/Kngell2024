<?php

declare(strict_types=1);
class QueryParameterRegionContext extends AbstractRegionContext
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

    public function providesExplicitChoice(): bool
    {
        return true;
    }

    public function getName(): string
    {
        return 'query_param';
    }
}