<?php

declare(strict_types=1);

use Psr\Log\LoggerInterface;

class HtmlPageCacheManager
{
    private const int DEFAULT_TTL = 300;
    private const string PAGE_PREFIX = 'html_page';
    private const string SECTION_PREFIX = 'html_section';

    public function __construct(
        private CacheInterface $cache,
        private EntityCacheKeyGeneratorInterface $keyGenerator,
        private string $entityClass,
        private ?LoggerInterface $logger = null,
    ) {
    }

    /**
     * Get cached HTML for a complete page.
     */
    public function getPage(string $pageIdentifier, array $context = []): ?string
    {
        $cacheKey = $this->buildPageCacheKey($pageIdentifier, $context);

        $html = $this->cache->get($cacheKey);

        if ($html !== null) {
            $this->logDebug('Page cache hit', ['page' => $pageIdentifier, 'key' => $cacheKey]);
        } else {
            $this->logDebug('Page cache miss', ['page' => $pageIdentifier, 'key' => $cacheKey]);
        }

        return $html;
    }

    /**
     * Store HTML for a complete page.
     */
    public function setPage(string $pageIdentifier, string $html, array $context = [], ?int $ttl = self::DEFAULT_TTL): bool
    {
        $cacheKey = $this->buildPageCacheKey($pageIdentifier, $context);

        $success = $this->cache->set($cacheKey, $html, $ttl);

        if ($success) {
            $this->logDebug('Page cached', ['page' => $pageIdentifier, 'key' => $cacheKey, 'ttl' => $ttl]);
        }

        return $success;
    }

    /**
     * Get cached HTML for a specific page section.
     */
    public function getSection(string $pageIdentifier, string $section, array $context = []): ?string
    {
        $cacheKey = $this->buildSectionCacheKey($pageIdentifier, $section, $context);

        $html = $this->cache->get($cacheKey);

        if ($html !== null) {
            $this->logDebug('Section cache hit', [
                'page' => $pageIdentifier,
                'section' => $section,
                'key' => $cacheKey,
            ]);
        }

        return $html;
    }

    /**
     * Store HTML for a specific page section.
     */
    public function setSection(string $pageIdentifier, string $section, string $html, array $context = [], ?int $ttl = self::DEFAULT_TTL): bool
    {
        $cacheKey = $this->buildSectionCacheKey($pageIdentifier, $section, $context);

        $success = $this->cache->set($cacheKey, $html, $ttl);

        if ($success) {
            $this->logDebug('Section cached', [
                'page' => $pageIdentifier,
                'section' => $section,
                'key' => $cacheKey,
                'ttl' => $ttl,
            ]);
        }

        return $success;
    }

    /**
     * Get multiple sections at once.
     *
     * @return array<string, string|null> Section name => HTML (null if not cached)
     */
    public function getSections(string $pageIdentifier, array $sections, array $context = []): array
    {
        $keys = [];
        $sectionKeyMap = [];

        foreach ($sections as $section) {
            $cacheKey = $this->buildSectionCacheKey($pageIdentifier, $section, $context);
            $keys[] = $cacheKey;
            $sectionKeyMap[$cacheKey] = $section;
        }

        // Multi-get if your cache supports it
        $cachedValues = $this->cache->getMultiple($keys);

        $result = [];
        foreach ($cachedValues as $cacheKey => $html) {
            $section = $sectionKeyMap[$cacheKey];
            $result[$section] = $html;
        }

        return $result;
    }

    /**
     * @param array<string, string> $sections Section name => HTML
     */
    public function setSections(string $pageIdentifier, array $sections, array $context = [], ?int $ttl = self::DEFAULT_TTL): bool
    {
        $items = [];

        foreach ($sections as $section => $html) {
            $cacheKey = $this->buildSectionCacheKey($pageIdentifier, $section, $context);
            $items[$cacheKey] = $html;
        }

        $success = $this->cache->setMultiple($items, $ttl);

        if ($success) {
            $this->logDebug('Multiple sections cached', [
                'page' => $pageIdentifier,
                'sections' => array_keys($sections),
                'count' => count($sections),
            ]);
        }

        return $success;
    }

    /**
     * Invalidate entire page cache.
     */
    public function invalidatePage(string $pageIdentifier, array $context = []): bool
    {
        $pageKey = $this->buildPageCacheKey($pageIdentifier, $context);

        $sectionPattern = $this->buildSectionPattern($pageIdentifier, $context);

        $success = $this->cache->delete($pageKey) &&
                   $this->cache->deletePattern($sectionPattern);

        if ($success) {
            $this->logDebug('Page invalidated', ['page' => $pageIdentifier, 'pattern' => $sectionPattern]);
        }

        return $success;
    }

    public function invalidateSection(string $pageIdentifier, string $section, array $context = []): bool
    {
        $cacheKey = $this->buildSectionCacheKey($pageIdentifier, $section, $context);

        $success = $this->cache->delete($cacheKey);

        if ($success) {
            $this->logDebug('Section invalidated', [
                'page' => $pageIdentifier,
                'section' => $section,
            ]);
        }

        return $success;
    }

    public function invalidateByEntity(Entity $entity): bool
    {
        $entityKey = $this->keyGenerator->getCacheKey($entity);
        $pattern = $this->buildEntityDependencyPattern($entityKey);
        $success = $this->cache->deletePattern($pattern);
        $this->logDebug('Invalidated pages by entity', [
            'entity' => get_class($entity),
            'entity_key' => $entityKey,
            'pattern' => $pattern,
        ]);

        return $success;
    }

    public function warmPage(string $pageIdentifier, callable $pageGenerator, array $context = [], ?int $ttl = self::DEFAULT_TTL): bool
    {
        try {
            $html = $pageGenerator();
            return $this->setPage($pageIdentifier, $html, $context, $ttl);
        } catch (Throwable $e) {
            $this->logError('Failed to warm page cache', [
                'page' => $pageIdentifier,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    public function warmSections(string $pageIdentifier, array $sectionGenerators, array $context = [], ?int $ttl = self::DEFAULT_TTL): array
    {
        $results = [];

        foreach ($sectionGenerators as $section => $generator) {
            try {
                $html = $generator();
                $success = $this->setSection($pageIdentifier, $section, $html, $context, $ttl);
                $results[$section] = $success;
            } catch (Throwable $e) {
                $this->logError('Failed to warm section cache', [
                    'page' => $pageIdentifier,
                    'section' => $section,
                    'error' => $e->getMessage(),
                ]);
                $results[$section] = false;
            }
        }

        return $results;
    }

    // =================== PRIVATE METHODS ===================

    private function buildPageCacheKey(string $pageIdentifier, array $context = []): string
    {
        $normalizedPage = $this->normalizeIdentifier($pageIdentifier);
        $contextHash = $this->hashContext($context);

        return sprintf(
            '%s_%s_%s',
            self::PAGE_PREFIX,
            $normalizedPage,
            $contextHash,
        );
    }

    private function buildSectionCacheKey(string $pageIdentifier, string $section, array $context = []): string
    {
        $normalizedPage = $this->normalizeIdentifier($pageIdentifier);
        $normalizedSection = $this->normalizeIdentifier($section);
        $contextHash = $this->hashContext($context);

        return sprintf(
            '%s_%s_%s_%s',
            self::SECTION_PREFIX,
            $normalizedPage,
            $normalizedSection,
            $contextHash,
        );
    }

    private function buildSectionPattern(string $pageIdentifier, array $context = []): string
    {
        $normalizedPage = $this->normalizeIdentifier($pageIdentifier);

        if (!empty($context)) {
            $contextHash = $this->hashContext($context);
            return sprintf('%s_%s_*_%s', self::SECTION_PREFIX, $normalizedPage, $contextHash);
        }

        return sprintf('%s_%s_*', self::SECTION_PREFIX, $normalizedPage);
    }

    private function buildEntityDependencyPattern(string $entityKey): string
    {
        return sprintf('*_%s_*', $entityKey);
    }

    private function normalizeIdentifier(string $identifier): string
    {
        $normalized = preg_replace('/[^a-zA-Z0-9]/', '_', $identifier);
        $normalized = preg_replace('/_+/', '_', $normalized);

        return trim($normalized, '_');
    }

    private function hashContext(array $context): string
    {
        if (empty($context)) {
            return 'default';
        }

        // Sort to ensure consistent hashing
        ksort($context);

        // Create a deterministic hash
        $contextString = json_encode($context);
        return substr(md5($contextString), 0, 8);
    }

    private function logDebug(string $message, array $context = []): void
    {
        $this->logger?->debug('[HtmlPageCache] ' . $message, $context);
    }

    private function logError(string $message, array $context = []): void
    {
        $this->logger?->error('[HtmlPageCache] ' . $message, $context);
    }
}