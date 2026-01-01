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
            // Use the providesExplicitChoice() method from trait
            if ($context->providesExplicitChoice() && $context->resolveRegion() !== null) {
                return true;
            }
        }

        return false;
    }

    public function getAllResolvedRegions(): array
    {
        $results = [];

        foreach ($this->contexts as $context) {
            $region = $context->resolveRegion();
            if ($region !== null) {
                $results[] = [
                    'context' => method_exists($context, 'getName') ? $context->getName() : get_class($context),
                    'region' => $region,
                    'priority' => $context->getPriority(),
                    'is_explicit' => method_exists($context, 'providesExplicitChoice') ?
                        ($context->providesExplicitChoice() && $region !== null) : false,
                ];
            }
        }

        return $results;
    }

    public function getRegionSource(): string
    {
        foreach ($this->contexts as $context) {
            $region = $context->resolveRegion();
            if ($region !== null) {
                return method_exists($context, 'getName') ? $context->getName() : 'unknown';
            }
        }

        return 'default';
    }

    public function getContextByName(string $name): ?RegionContextResolutionInterface
    {
        foreach ($this->contexts as $context) {
            $contextName = method_exists($context, 'getName') ? $context->getName() : '';
            if ($contextName === $name) {
                return $context;
            }
        }

        return null;
    }
}