<?php

declare(strict_types=1);

abstract class AbstractListDecorator extends AbstractAdminHtmlDecorator
{
    private ?int $cachedTotalCount = null;
    private array $cachedEntityCounts = [];
    private array $paginations = [];

    public function __construct(
        protected readonly PaginatedCacheFactory $cacheFactory,
        protected readonly PaginationStateService $paginationService,
        protected readonly IconBuilder $iconBuilder,
        AdminMainHeaderFactory $adminHeaderFactory,
    ) {
        parent::__construct($adminHeaderFactory);
        $this->withFilters = true;
    }

    public function page(): array
    {
        $target = $this->getTarget();
        $this->validateTarget($target);

        $paginationData = $this->paginationService->getPaginationData($target->request);
        $items = $this->fetchItems($paginationData);
        $totalRecords = $this->getTotalRecords();

        $instance = $this->createInstance($items, $target, $this->paginations);

        $htmlVariables = parent::page()
            + $this->buildHeaderSection($target)
            + ['entityTable' => $instance->getHtmlElements()]
            + ['entityCounts' => $this->cachedEntityCounts];

        $adapter = $this->getAdapter();

        if ($adapter instanceof PaginatedEntityAdapterInterface) {
            if ($this->requiresPagination($totalRecords, $paginationData->recordsPerPage)) {
                $totalPages = $this->calculateTotalPages($totalRecords, $paginationData->recordsPerPage);
                $htmlVariables['pagination'] = $this->buildPagination(
                    $target,
                    $paginationData,
                    $totalRecords,
                    $totalPages,
                )->getPagination();
            }
        }

        return $htmlVariables;
    }

    // ─── Existing methods ──────────────────────────────────

    public function getEntityTotalCount(string $entityClass): int
    {
        return $this->cachedEntityCounts[$entityClass] ?? 0;
    }

    public function getEntityCounts(): array
    {
        return $this->cachedEntityCounts;
    }

    // ─── Per-Entity Pagination ─────────────────────────────

    protected function buildPerEntityPaginations(
        array $adapters,
        Controller $target,
        PaginationData $paginationData,
    ): void {
        $this->paginations = [];

        foreach ($adapters as $adapter) {
            if (!$adapter instanceof PaginatedEntityAdapterInterface) {
                continue;
            }

            $entityClass = $adapter->getEntityClass();
            $entityCount = $this->cachedEntityCounts[$entityClass] ?? 0;

            // Skip pagination for entities below threshold
            if (!$this->requiresPagination($entityCount, $paginationData->recordsPerPage)) {
                continue;
            }

            $totalPages = $this->calculateTotalPages($entityCount, $paginationData->recordsPerPage);

            $this->paginations[$entityClass] = $this->buildPagination(
                $target,
                $paginationData,
                $entityCount,
                $totalPages,
            )->getPagination();
        }
    }

    abstract protected function validateTarget(Controller $target): void;

    /**
     * @return PaginatedEntityAdapterInterface|array<PaginatedEntityAdapterInterface>
     */
    abstract protected function getAdapter(): PaginatedEntityAdapterInterface|array;

    abstract protected function getCacheKey(string $entityClass): string;

    /**
     * Create the list instance.
     *
     * @param array<string, array<int, object>>|array<int, object> $items
     * @param array<string, array> $paginations Per-entity pagination data keyed by entity class
     */
    abstract protected function createInstance(
        array $items,
        Controller $target,
        array $paginations = [],
    ): AdminListElementsInterface;

    protected function createCache(PaginatedEntityAdapterInterface $adapter, string $cacheKey): PaginatedCacheServiceInterface
    {
        return $this->cacheFactory->create($adapter, $cacheKey, true);
    }

    protected function fetchItems(PaginationData $paginationData): array
    {
        $adapter = $this->getAdapter();

        if ($adapter instanceof PaginatedEntityAdapterInterface) {
            return $this->fetchItemsFromAdapter($adapter, $paginationData);
        }

        if (is_array($adapter)) {
            return $this->fetchItemsFromAdapters($adapter, $paginationData, $this->getTarget());
        }

        throw new RuntimeException('Invalid adapter configuration');
    }

    protected function fetchItemsFromAdapter(
        PaginatedEntityAdapterInterface $adapter,
        PaginationData $paginationData,
    ): array {
        $cacheKey = $this->getCacheKey($adapter->getEntityClass());
        $cache = $this->createCache($adapter, $cacheKey);

        $count = $cache->getTotalCount();
        $this->cachedTotalCount = $count;
        $this->cachedEntityCounts[$adapter->getEntityClass()] = $count;

        return $cache->getEntities(
            $paginationData->currentPage,
            $paginationData->recordsPerPage,
        );
    }

    /**
     * @param array<PaginatedEntityAdapterInterface> $adapters
     */
    protected function fetchItemsFromAdapters(
        array $adapters,
        PaginationData $paginationData,
        Controller $target,
    ): array {
        $entities = [];
        $totalCount = 0;

        foreach ($adapters as $adapter) {
            if (!$adapter instanceof PaginatedEntityAdapterInterface) {
                continue;
            }

            $entityClass = $adapter->getEntityClass();
            $cacheKey = $this->getCacheKey($entityClass);
            $cache = $this->createCache($adapter, $cacheKey);

            $count = $cache->getTotalCount();
            $this->cachedEntityCounts[$entityClass] = $count;
            $totalCount += $count;

            $entities[$entityClass] = $cache->getEntities(
                $paginationData->currentPage,
                $paginationData->recordsPerPage,
            );
        }

        $this->cachedTotalCount = $totalCount;

        $this->buildPerEntityPaginations($adapters, $target, $paginationData);

        return $entities;
    }

    protected function getTotalRecords(): int
    {
        if ($this->cachedTotalCount === null) {
            throw new LogicException('fetchItems() must be called before getTotalRecords()');
        }
        return $this->cachedTotalCount;
    }

    protected function requiresPagination(int $totalRecords, int $recordsPerPage): bool
    {
        return $totalRecords > $recordsPerPage;
    }

    protected function calculateTotalPages(int $totalRecords, int $recordsPerPage): int
    {
        if ($recordsPerPage <= 0) {
            throw new InvalidArgumentException('Records per page must be greater than zero');
        }
        return (int) ceil($totalRecords / $recordsPerPage);
    }

    protected function buildPagination(
        Controller $target,
        PaginationData $paginationData,
        int $totalRecords,
        int $totalPages,
    ): Pagination {
        return new Pagination(
            $target->getBuilder(),
            $this->iconBuilder,
            $paginationData->currentPage,
            $paginationData->recordsPerPage,
            $totalRecords,
            $totalPages,
            $target->request,
            $paginationData->allowedPageSizes,
        );
    }
}