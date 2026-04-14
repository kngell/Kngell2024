<?php

declare(strict_types=1);

use Psr\Log\LoggerInterface;

/**
 * @template T of Entity
 */
abstract class AbstractBaseSectionService implements CacheableSectionServiceInterface
{
    protected array $widths = [];

    public function __construct(
        protected ImageOptimizerFactory $imageOptimizerFactory,
        protected readonly HtmlSectionCacheManager $cache,
        protected ?LoggerInterface $logger = null,
    ) {
    }

    public function clearAllCaches(): bool
    {
        return $this->cache->clearSection(static::class);
    }

    public function getCacheStats(): array
    {
        return $this->cache->getStatistics(static::class);
    }

    public function warmupCache(array $identifiers): int
    {
        $warmed = 0;
        foreach ($identifiers as $identifier) {
            $warmed += $this->warmupIdentifier($identifier);
        }
        return $warmed;
    }

    /**
     * @param array $widths
     *
     * @return SmallBannerService
     */
    public function setWidths(array $widths): SmallBannerService
    {
        $this->widths = $widths;

        return $this;
    }

    /**
     * Create a response object.
     *
     * @param array $image Image data
     * @param T|null $entity Entity or null for default
     * @param bool $isDefault Whether this is a default response
     *
     * @return EntityResponseInterface
     */
    abstract protected function createResponse(array $image, ?Entity $entity, bool $isDefault): EntityResponseInterface;

    /**
     * Warm up a single identifier (page name or ID).
     */
    abstract protected function warmupIdentifier(string $identifier): int;

    /**
     * Get optimized image URL.
     */
    protected function getOptimizedUrl(ImageOptimizer $optimizer, ?string $imagePath, int $width, array $options = []): string
    {
        if ($imagePath === null) {
            return '';
        }
        return $optimizer->optimize($imagePath, $width, $options)->getUrl();
    }

    /**
     * Get optimized image height.
     */
    protected function getOptimizedHeight(ImageOptimizer $optimizer, ?string $imagePath, int $width, array $options = []): ?int
    {
        return $optimizer->optimize($imagePath, $width, $options)->getHeight();
    }

    /**
     * Generate srcset string for responsive images.
     */
    protected function generateSrcSet(
        ImageOptimizer $optimizer,
        ?string $imagePath,
        array $widths,
        array $options = [],
    ): string {
        $srcset = [];
        foreach ($widths as $width) {
            $srcset[] = $this->getOptimizedUrl($optimizer, $imagePath, $width, $options) . " {$width}w";
        }
        return implode(', ', $srcset);
    }

    /**
     * Log debug message.
     */
    protected function logDebug(string $message, array $context = []): void
    {
        $this->logger?->debug($message, $context);
    }

    /**
     * Log error message.
     */
    protected function logError(string $message, array $context = []): void
    {
        $this->logger?->error($message, $context);
    }
}