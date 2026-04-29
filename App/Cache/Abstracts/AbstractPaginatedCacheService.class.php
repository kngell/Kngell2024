<?php

declare(strict_types=1);

use Psr\Log\LoggerInterface;

abstract class AbstractPaginatedCacheService implements PaginatedCachingServiceInterface
{
    protected string $entityClass;

    public function __construct(
        protected EntityCacheManager $entityCacheManager,
        protected paginationCacheManager $paginationCacheManager,
        protected ?LoggerInterface $logger = null,
    ) {
        $this->validateEntityClass();
    }

    public function getEntities(int $page, int $perPage, bool $forceRefresh = false): array
    {
        $pageData = $this->paginationCacheManager->getPageKeys(
            $page,
            $perPage,
            fn () => $this->getAllEntityKeys($page, $perPage),
        );

        $identifiers = $pageData['identifiers'];

        if (empty($identifiers)) {
            return [];
        }
        return $this->entityCacheManager->getEntities(
            $identifiers,
            fn ($missingIds) => $this->getEntitiesByKeys($missingIds),
        );
    }

    public function getEntity(string $identifier, bool $forceRefresh = false): ?Entity
    {
        if ($forceRefresh) {
            $entity = $this->getEntityFromSource($identifier);
            if ($entity) {
                $this->entityCacheManager->cacheEntity($entity);
            }
            return $entity;
        }

        return $this->entityCacheManager->getEntity(
            $identifier,
            fn ($id) => $this->getEntityFromSource($id),
        );
    }

    public function cacheEntity(Entity $entity): bool
    {
        return $this->entityCacheManager->cacheEntity($entity);
    }

    public function getTotalCount(bool $forceRefresh = false): int
    {
        return $this->paginationCacheManager->getTotalCount(
            fn () => $this->getTotalCountFromSource(),
            $forceRefresh,
        );
    }

    public function invalidateCount(): void
    {
        $this->paginationCacheManager->invalidateCount();
    }

    public function invalidateEntity(string $identifier): bool
    {
        return $this->entityCacheManager->invalidateEntity($identifier);
    }

    public function invalidateAll(): void
    {
        // Delegate entirely to the manager — it owns the tag naming
        $this->paginationCacheManager->invalidateAll();

        $this->logDebug('Global cache invalidation triggered', [
            'entity' => $this->entityClass,
        ]);
    }

    public function cacheEntities(array $entities): int
    {
        return $this->entityCacheManager->cacheEntities($entities);
    }

    public function warmPageCache(int $page, int $perPage): bool
    {
        try {
            $this->getEntities($page, $perPage);
            return true;
        } catch (Throwable $e) {
            $this->logError('Failed to warm page cache', [
                'page' => $page,
                'perPage' => $perPage,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    public function getEntityClass(): string
    {
        return $this->entityClass;
    }

    // =================== ABSTRACT METHODS ===================

    abstract protected function getAllEntityKeys(int $page, int $perPage): array;

    abstract protected function getEntitiesByKeys(array $identifiers): array;

    abstract protected function getEntityIdentifier(Entity $entity): string;

    abstract protected function getTotalCountFromSource(): int;

    // abstract protected function generatePageCacheKey(int $page, int $perPage): string;

    // abstract protected function generateEntityCacheKey(string $identifier): string;

    // abstract protected function generateCountCacheKey(): string;

    // =================== PROTECTED HELPERS ===================

    protected function getEntityFromSource(string $identifier): ?Entity
    {
        $entities = $this->getEntitiesByKeys([$identifier]);
        return $entities[0] ?? null;
    }

    protected function logDebug(string $message, array $context = []): void
    {
        $this->logger?->debug($message, $context);
    }

    protected function logError(string $message, array $context = []): void
    {
        $this->logger?->error($message, $context);
    }

    // =================== PRIVATE HELPERS ===================

    private function validateEntityClass(): void
    {
        if (!class_exists($this->entityClass)) {
            throw new RuntimeException(sprintf(
                'Entity class %s does not exist',
                $this->entityClass,
            ));
        }

        if (!is_subclass_of($this->entityClass, Entity::class)) {
            throw new RuntimeException(sprintf(
                'Entity class %s must extend Entity',
                $this->entityClass,
            ));
        }
    }
}