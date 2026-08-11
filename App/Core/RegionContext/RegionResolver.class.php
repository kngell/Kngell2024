<?php

declare(strict_types=1);

class RegionResolver
{
    private const CACHE_KEY = 'resolved_region_code';

    /** @var RegionContextResolutionInterface[] */
    private array $contexts = [];

    private ?string $cachedResolution = null;
    private ?bool $cachedExplicit = null;
    private CacheInterface $cache;

    public function __construct(
        CacheInterface $cache,
        RegionContextResolutionInterface ...$contexts,
    ) {
        $this->cache = $cache;
        $this->contexts = $contexts;
        usort($this->contexts, fn ($a, $b) => $b->getPriority() <=> $a->getPriority());
    }

    public function resolve(): ?string
    {
        $data = $this->resolveWithExplicit();
        return $data['region'] ?? null;
    }

    public function hasExplicitRegion(): bool
    {
        $data = $this->resolveWithExplicit();
        return $data['explicit'] ?? false;
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

    public function clearCache(): void
    {
        $this->cachedResolution = null;
        $this->cachedExplicit = null;
        $this->cache->delete(self::CACHE_KEY);
    }

    /**
     * Resolve region and explicit flag together (cached together).
     */
    private function resolveWithExplicit(): array
    {
        // Check if already resolved in this request
        if ($this->cachedResolution !== null) {
            return [
                'region' => $this->cachedResolution,
                'explicit' => $this->cachedExplicit ?? false,
            ];
        }

        // Check persistent cache
        $cached = $this->cache->get(self::CACHE_KEY);
        if ($cached !== null && is_array($cached)) {
            $this->cachedResolution = $cached['region'];
            $this->cachedExplicit = $cached['explicit'] ?? false;
            return $cached;
        }

        // Run expensive resolution - iterate through all contexts
        $region = null;
        $explicit = false;

        foreach ($this->contexts as $context) {
            $region = $context->resolveRegion();
            if ($region) {
                // Check if this context provides explicit user choice
                $explicit = method_exists($context, 'providesExplicitChoice')
                    ? $context->providesExplicitChoice()
                    : false;
                break;
            }
        }

        $result = [
            'region' => $region,
            'explicit' => $explicit,
        ];

        // Cache the result
        if ($region !== null) {
            $this->cache->set(self::CACHE_KEY, $result, 3600);
        }

        $this->cachedResolution = $region;
        $this->cachedExplicit = $explicit;
        return $result;
    }
}