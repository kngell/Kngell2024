<?php

declare(strict_types=1);

use Psr\Log\LoggerInterface;

final class PaginatedCacheService extends AbstractPaginatedCacheService
{
    public function __construct(
        EntityCacheManager $entityCache,
        PaginationCacheManager $paginationCache,
        private PaginatedEntityAdapterInterface $adapter,
        private ?PageTrackerInterface $pageTracker = null,
        ?LoggerInterface $logger = null,
    ) {
        $this->entityClass = $adapter->getEntityClass();
        parent::__construct($entityCache, $paginationCache, $logger);
    }

    public function getEntities(int $page, int $perPage, bool $forceRefresh = false): array
    {
        $entities = parent::getEntities($page, $perPage, $forceRefresh);

        // Optional page tracking
        if ($this->pageTracker) {
            foreach ($entities as $entity) {
                $identifier = $this->getEntityIdentifier($entity);
                $this->pageTracker->track($identifier, $page, $perPage);
            }
        }

        return $entities;
    }

    public function invalidateEntity(string $identifier): bool
    {
        // $identifier = $this->adapter->normalizeIdentifier($identifier);
        $this->logDebug('Entity cache invalidated', ['identifier' => $identifier]);
        return parent::invalidateEntity($identifier);
    }

    public function invalidateEntityWithPages(string $identifier): array
    {
        // $identifier = $this->adapter->normalizeIdentifier($identifier);
        $this->invalidateEntity($identifier);

        if ($this->pageTracker) {
            return $this->pageTracker->clearPages(
                $identifier,
                $this->paginationCacheManager,
            );
        }

        return [];
    }

    public function invalidateEntityAndAllPages(string $identifier): array
    {
        // $identifier = $this->adapter->normalizeIdentifier($identifier);
        $this->invalidateEntity($identifier);
        $this->invalidateAll();

        return ['all_pages_cleared'];
    }

    // All other methods remain generic
    public function clearPageCache(int $page, int $perPage): bool
    {
        return $this->paginationCacheManager->clearPage($page, $perPage);
    }

    public function getPageCache(): CacheInterface
    {
        return $this->paginationCacheManager->getPageCache();
    }

    public function getEntityIdentifier(Entity $entity): string
    {
        return $this->paginationCacheManager->getCacheIdentifier($entity);
    }

    protected function getAllEntityKeys(int $page, int $perPage): array
    {
        return $this->adapter->getAllKeys($page, $perPage);
    }

    protected function getEntitiesByKeys(array $identifiers): array
    {
        return $this->adapter->getEntitiesByIdentifiers($identifiers);
    }

    protected function getTotalCountFromSource(): int
    {
        return $this->adapter->getTotalCount();
    }

    // protected function generatePageCacheKey(int $page, int $perPage): string
    // {
    //     return $this->paginationCacheManager->generatePageCacheKey($page, $perPage);
    // }

    // protected function generateEntityCacheKey(string $identifier): string
    // {
    //     return $this->paginationCacheManager->generateEntityCacheKey($identifier);
    // }

    // protected function generateCountCacheKey(): string
    // {
    //     return $this->paginationCacheManager->generateCountCacheKey();
    // }
}