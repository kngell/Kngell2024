<?php

declare(strict_types=1);

final class PostCachingService extends AbstractPaginationCachingService
{
    private const ENTITY_TTL = 3600; // 1 hour

    public function __construct(
        EntityCachingServiceInterface $entityCache,
        CacheInterface $pageCache,
        private PostModel $postModel,
        ?LoggerInterface $logger = null,
    ) {
        $this->entityClass = Post::class;
        parent::__construct($entityCache, $pageCache, $logger);
    }

    protected function getModel(): object
    {
        return $this->postModel;
    }

    protected function getAllEntityKeys(int $page, int $perPage): array
    {
        $results = $this->postModel->getAllPostKeys($page, $perPage);
        return array_column($results, 'slug');
    }

    protected function getEntitiesByKeys(array $identifiers): array
    {
        return $this->postModel->getPostsBySlugs($identifiers);
    }

    protected function getEntityIdentifier(object $entity): string
    {
        if (!$entity instanceof Post) {
            throw new InvalidArgumentException('Entity must be a Post');
        }

        return $entity->getSlug();
    }

    protected function getTotalCountFromSource(): int
    {
        return $this->postModel->count();
    }

    protected function generatePageCacheKey(int $page, int $perPage): string
    {
        $className = str_replace('\\', '_', $this->entityClass);
        return "page_{$className}_{$page}_{$perPage}";
    }

    protected function generateEntityCacheKey(string $identifier): string
    {
        $className = str_replace('\\', '_', $this->entityClass);
        $sanitizedId = str_replace(['-', ' ', '/'], '_', $identifier);
        return "entity_{$className}_{$sanitizedId}";
    }

    protected function generateCountCacheKey(): string
    {
        $className = str_replace('\\', '_', $this->entityClass);
        return "count_{$className}";
    }
}