<?php

declare(strict_types=1);
class HeaderRegionContext extends AbstractRegionContext
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

    public function providesExplicitChoice(): bool
    {
        return true;
    }

    public function getName(): string
    {
        return 'http_header';
    }
}