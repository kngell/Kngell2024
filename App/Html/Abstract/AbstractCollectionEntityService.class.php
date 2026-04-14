<?php

declare(strict_types=1);

/**
 * @template T of Entity
 */
abstract class AbstractCollectionEntityService extends AbstractBaseSectionService implements CollectionEntityServiceInterface
{
    public function __construct(
        ImageOptimizerFactory $imageOptimizerFactory,
        HtmlSectionCacheManager $cache,
    ) {
        parent::__construct($imageOptimizerFactory, $cache);
    }

    public function getForPage(?string $page = null): array
    {
        $page = $page ?? 'index';

        try {
            $entities = $this->cache->getEntitiesForPage(
                $page,
                static::class,
                fn ($p) => $this->fetchEntitiesFromDbForPage($p),    // Page loader
                fn ($ids) => $this->fetchEntitiesFromDbByIds($ids),   // IDs loader
            );

            if (empty($entities)) {
                return $this->getDefaultResponse();
            }

            return $this->buildResponses($entities);
        } catch (Exception $e) {
            $this->logError('Failed to get for page', [
                'page' => $page,
                'error' => $e->getMessage(),
            ]);
            return $this->getDefaultResponse();
        }
    }

    /**
     * @return array<EntityResponseInterface>
     */
    abstract public function getDefaultResponse(): array;

    /**
     * Fetch entities from database for a page.
     *
     * @return array<T>
     */
    abstract protected function fetchEntitiesFromDbForPage(string $page): array;

    /**
     * Fetch entities from database by IDs.
     *
     * @param array<int> $ids
     *
     * @return array<T>
     */
    abstract protected function fetchEntitiesFromDbByIds(array $ids): array;

    /**
     * @param array<T> $entities
     *
     * @return array<EntityResponseInterface>
     */
    abstract protected function buildResponses(array $entities): array;

    protected function warmupIdentifier(string $identifier): int
    {
        // For collection services, identifier is page name
        $entities = $this->fetchEntitiesFromDbForPage($identifier);
        if (!empty($entities)) {
            // This will cache both entities and page mapping
            $this->cache->getEntitiesForPage(
                $identifier,
                static::class,
                fn ($p) => $entities,  // Return already fetched entities
                fn ($ids) => $this->fetchEntitiesFromDbByIds($ids),
            );
            return count($entities);
        }
        return 0;
    }
}