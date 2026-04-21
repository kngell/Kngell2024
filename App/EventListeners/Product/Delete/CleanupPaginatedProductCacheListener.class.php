<?php

declare(strict_types=1);

class CleanupPaginatedProductCacheListener implements EventListenerInterface
{
    public function __construct(
        private PaginatedCacheFactory $factory,
        private ProductShowModel $model,
    ) {
    }

    public function handle(EventInterface $event): ?object
    {
        $payload = $event->getParams();
        $productId = $payload['data']['pdt_id'] ?? null;

        if (!$productId) {
            return null;
        }

        $paginatedCache = $this->factory->createProductCache($this->model);

        $clearedPages = $paginatedCache->invalidateProductWithPages((string) $productId);

        $deletionType = $payload['deletion_type'] ?? 'unknown';
        error_log(sprintf(
            'Product cache cleaned up: p_%s (Type: %s, Pages cleared: %d)',
            $productId,
            $deletionType,
            count($clearedPages),
        ));

        return null;
    }
}