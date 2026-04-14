<?php

declare(strict_types=1);

/**
 * @template T of Entity
 */
abstract class AbstractSingleEntityService extends AbstractBaseSectionService implements SingleEntityServiceInterface
{
    public function __construct(
        ImageOptimizerFactory $imageOptimizerFactory,
        HtmlSectionCacheManager $cache,
    ) {
        parent::__construct($imageOptimizerFactory, $cache);
    }

    public function getForPage(?string $page = null): EntityResponseInterface
    {
        $page = $page ?? 'index';

        try {
            $entity = $this->cache->getEntityForPage(
                $page,
                static::class,
                fn ($p) => $this->fetchEntityFromDb($p),
                fn ($id) => $this->fetchEntityByIdFromDb($id),
            );

            if (!$entity) {
                return $this->getDefaultResponse();
            }

            return $this->createResponse(
                image: $this->buildResponsiveImage($entity),
                entity: $entity,
                isDefault: false,
            );
        } catch (Exception $e) {
            $this->logError('Failed to get for page', [
                'page' => $page,
                'error' => $e->getMessage(),
            ]);
            return $this->getDefaultResponse();
        }
    }

    /**
     * @return EntityResponseInterface
     */
    abstract public function getDefaultResponse(): EntityResponseInterface;

    /**
     * Fetch entity from database by page.
     *
     * @return T|null
     */
    abstract protected function fetchEntityFromDb(string $page): ?Entity;

    /**
     * Fetch entity from database by ID.
     *
     * @return T|null
     */
    abstract protected function fetchEntityByIdFromDb(string $id): ?Entity;

    /**
     * @param T $entity
     */
    abstract protected function buildResponsiveImage(Entity $entity): array;

    abstract protected function getDefaultImageData(): array;

    protected function warmupIdentifier(string $identifier): int
    {
        // For single entity services, identifier could be page name
        $entity = $this->fetchEntityFromDb($identifier);
        if ($entity) {
            // This will cache both the entity and the page mapping
            $this->cache->getEntityForPage(
                $identifier,
                static::class,
                fn ($p) => $entity,  // Return already fetched entity
                fn ($id) => $this->fetchEntityByIdFromDb($id),
            );
            return 1;
        }
        return 0;
    }
}