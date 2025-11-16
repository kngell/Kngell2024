<?php

declare(strict_types=1);

final class RegionContext implements RegionContextInterface
{
    private const FALLBACK_REGION = 'EU';

    private ?string $resolvedRegion = null;

    public function __construct(
        private RegionResolver $regionResolver,
        private RegionProviderService $regionProvider,
        private string $defaultRegion,
    ) {
    }

    public function getRegionCode(): string
    {
        if ($this->resolvedRegion === null) {
            $this->resolvedRegion = $this->resolveRegion();
        }

        return $this->resolvedRegion;
    }

    public function isRegionExplicit(): bool
    {
        return $this->regionResolver->hasExplicitRegion();
    }

    private function resolveRegion(): string
    {
        $candidate = $this->regionResolver->resolve();

        if ($candidate && $this->regionProvider->isValidRegion($candidate)) {
            return $candidate;
        }

        // Validate default region too
        return $this->regionProvider->isValidRegion($this->defaultRegion)
            ? $this->defaultRegion
            : self::FALLBACK_REGION;
    }
}