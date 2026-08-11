<?php

declare(strict_types=1);

use Psr\Log\LoggerInterface;

/**
 * @method void logTiming(string $label)
 * @method LoggerInterface logger()
 */
trait HtmlPageCacheableTrait
{
    private ?HtmlPageCacheManager $htmlCache = null;
    private ?HtmlPageCacheFactory $htmlCacheFactory = null;
    private bool $htmlCacheInitialized = false;

    // ============================================
    // CACHE INITIALIZATION - ORIGINAL SIGNATURE
    // ============================================

    protected function initializeHtmlCache(HtmlPageCacheFactory $htmlCacheFactory): self
    {
        if ($this->htmlCacheInitialized) {
            return $this;
        }

        $this->htmlCacheFactory = $htmlCacheFactory;
        $this->htmlCache = $htmlCacheFactory->createPageCache();
        $this->htmlCacheInitialized = true;
        return $this;
    }

    protected function getHtmlCache(): HtmlPageCacheManager
    {
        if ($this->htmlCache === null) {
            throw new RuntimeException(
                'HTML cache not initialized. Call initializeHtmlCache() in your constructor.',
            );
        }
        return $this->htmlCache;
    }

    protected function hasHtmlCache(): bool
    {
        return $this->htmlCacheInitialized && $this->htmlCache !== null;
    }

    // ============================================
    // IDENTIFIER GENERATION
    // ============================================

    protected function getPageIdentifier(?string $controller = null, ?string $action = null): string
    {
        if ($controller !== null && $action !== null) {
            return $this->formatIdentifier($controller, $action);
        }

        $routeInfo = $this->app?->get('current.route');
        if ($routeInfo instanceof RouteInfo) {
            $controller = $routeInfo->getController();
            $action = $routeInfo->getMethod()->getName();
            return $this->formatIdentifier($controller, $action);
        }

        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
        $controller = $trace[1]['class'] ?? get_class($this);
        $action = $trace[1]['function'] ?? 'index';

        return $this->formatIdentifier($controller, $action);
    }

    // ============================================
    // CORE CACHE OPERATIONS - GET/SET
    // ============================================

    protected function getCachedPage(string $identifier): ?string
    {
        $cache = $this->getHtmlCache();
        return $cache->getPage($identifier, []);
    }

    protected function setCachedPage(string $identifier, string $html, ?int $ttl = null): bool
    {
        $cache = $this->getHtmlCache();
        return $cache->setPage($identifier, $html, [], $ttl);
    }

    protected function getCachedSection(string $identifier, string $section): ?string
    {
        $cache = $this->getHtmlCache();
        return $cache->getSection($identifier, $section, []);
    }

    protected function setCachedSection(string $identifier, string $section, string $html, ?int $ttl = null): bool
    {
        $cache = $this->getHtmlCache();
        return $cache->setSection($identifier, $section, $html, [], $ttl);
    }

    protected function getCachedSections(string $identifier, array $sections): array
    {
        $cache = $this->getHtmlCache();
        return $cache->getSections($identifier, $sections, []);
    }

    protected function setCachedSections(string $identifier, array $sections, ?int $ttl = null): bool
    {
        $cache = $this->getHtmlCache();
        return $cache->setSections($identifier, $sections, [], $ttl);
    }

    // ============================================
    // CORE CACHE OPERATIONS - DELETE
    // ============================================

    protected function deleteCachedPage(string $identifier): bool
    {
        $cache = $this->getHtmlCache();
        return $cache->invalidatePage($identifier, []);
    }

    protected function deleteCachedSection(string $identifier, string $section): bool
    {
        $cache = $this->getHtmlCache();
        return $cache->invalidateSection($identifier, $section, []);
    }

    protected function deleteCachedPattern(string $pattern): bool
    {
        $cache = $this->getHtmlCache();
        if (method_exists($cache, 'deletePattern')) {
            return $cache->deletePattern($pattern);
        }
        return false;
    }

    protected function deleteAllContexts(string $identifier): bool
    {
        $cache = $this->getHtmlCache();

        if (method_exists($cache, 'deletePattern')) {
            $pattern = 'html_page_' . $identifier . '_*';
            return $cache->deletePattern($pattern);
        }

        return $cache->invalidatePage($identifier, []);
    }

    // ============================================
    // HIGH-LEVEL CACHE PAGE METHOD
    // ============================================

    protected function cachePage(callable $pageGenerator, ?int $ttl = null, ?string $customIdentifier = null): mixed
    {
        $identifier = $customIdentifier ?? $this->getPageIdentifier();

        // Try to get from cache
        $cached = $this->getCachedPage($identifier);
        if ($cached !== null) {
            $this->logTiming('Page served from cache: ' . $identifier);
            return $cached;
        }

        // Generate and cache
        $this->logTiming('Building page: ' . $identifier);
        $result = $pageGenerator();
        $this->setCachedPage($identifier, $result, $ttl);

        return $result;
    }

    protected function cacheSections(array $sectionGenerators, ?int $ttl = null, ?string $customIdentifier = null): array
    {
        $identifier = $customIdentifier ?? $this->getPageIdentifier();

        $results = [];
        $missingSections = [];
        $sectionNames = array_keys($sectionGenerators);

        // Try to get all sections
        $cachedSections = $this->getCachedSections($identifier, $sectionNames);

        foreach ($sectionGenerators as $section => $generator) {
            if (isset($cachedSections[$section]) && $cachedSections[$section] !== null) {
                $results[$section] = $cachedSections[$section];
            } else {
                $missingSections[$section] = $generator;
            }
        }

        // Build and cache missing sections
        if (!empty($missingSections)) {
            $builtSections = [];
            foreach ($missingSections as $section => $generator) {
                $builtSections[$section] = $generator();
            }

            $this->setCachedSections($identifier, $builtSections, $ttl);
            $results = array_merge($results, $builtSections);
        }

        return $results;
    }

    private function formatIdentifier(string $controller, string $action): string
    {
        $controllerName = str_replace('Controller', '', basename(str_replace('\\', '/', $controller)));
        $actionName = strtolower($action);
        return strtolower($controllerName . '_' . $actionName);
    }
}