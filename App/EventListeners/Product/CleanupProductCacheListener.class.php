<?php

declare(strict_types=1);

class CleanupProductCacheListener implements EventListenerInterface
{
    public function __construct(
        private PaginatedCacheFactory $factory,
        private ProductShowModel $model,
        private CacheWarmer $cacheWarmer,
        private PaginationStateService $stateService,
    ) {
    }

    public function handle(EventInterface $event): ?object
    {
        $params = $event->getParams();
        $eventName = $event->getName();
        $productCache = $this->factory->createProductCache($this->model);

        if ($eventName === 'product.created' || $eventName === 'product.deleted' || isset($params['is_create']) || isset($params['is_delete'])) {
            $productCache->getPageCache()->invalidateTags(['pages_ProductShow']);
            $productCache->invalidateAll();

            // Handle specific entity removal if deleting
            $oldProduct = $params['model_data']['old_entity_snapshot'] ?? null;
            if ($oldProduct) {
                $identifier = $productCache->getEntityIdentifier($oldProduct);
                $productCache->invalidateEntity($identifier);
            }
            $this->cacheWarmer->warmCommonViews($productCache, $this->stateService);

            return null;
        }

        // CASE 2: Content updates
        $oldProduct = $params['model_data']['old_entity_snapshot'] ?? null;
        if ($oldProduct) {
            $identifier = $productCache->getEntityIdentifier($oldProduct);
            $productCache->invalidateProductWithPages($identifier);
        }

        return null;
    }
}