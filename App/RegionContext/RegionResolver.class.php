<?php

declare(strict_types=1);

class RegionResolver
{
    /** @var RegionContextResolutionInterface[] */
    private array $contexts = [];

    public function __construct(RegionContextResolutionInterface ...$contexts)
    {
        $this->contexts = $contexts;
        usort($this->contexts, fn ($a, $b) => $b->getPriority() <=> $a->getPriority());
    }

    public function resolve(): ?string
    {
        foreach ($this->contexts as $context) {
            $region = $context->resolveRegion();
            if ($region) {
                return $region;
            }
        }

        return null;
    }

    public function hasExplicitRegion(): bool
    {
        foreach ($this->contexts as $context) {
            if (($context instanceof QueryParameterRegionContext ||
                 $context instanceof HeaderRegionContext) &&
                $context->resolveRegion() !== null) {
                return true;
            }
        }

        return false;
    }
}