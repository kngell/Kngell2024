<?php

declare(strict_types=1);

abstract class AbstractListDecorator extends AbstractAdminHtmlDecorator
{
    // ─── Properties ───────────────────────────────────────────

    private ?int $cachedTotalCount = null;

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

        $table = $this->createTableInstance($items, $target);

        $htmlVariables = parent::page()
            + $this->buildHeaderSection($target)
            + ['entityTable' => $table->getTable()];

        if ($this->requiresPagination($totalRecords, $paginationData->recordsPerPage)) {
            $totalPages = $this->calculateTotalPages($totalRecords, $paginationData->recordsPerPage);
            $htmlVariables['pagination'] = $this->buildPagination(
                $target,
                $paginationData,
                $totalRecords,
                $totalPages,
            )->getPagination();
        }

        return $htmlVariables;
    }

    // ─── Abstract Methods ─────────────────────────────────────

    /** Ensure the target controller is the expected type */
    abstract protected function validateTarget(Controller $target): void;

    /** e.g. new HeroPaginatedAdapter($this->model) */
    abstract protected function getAdapter(): PaginatedEntityAdapterInterface;

    /** e.g. 'heroes' or 'products' */
    abstract protected function getCacheKey(): string;

    /** Return a table instance that implements a getTable() method */
    abstract protected function createTableInstance(array $items, Controller $target): ListTableInterface;

    // ─── Data Fetching ────────────────────────────────────────

    protected function createCache(): PaginatedCachingServiceInterface
    {
        return $this->cacheFactory->create(
            $this->getAdapter(),
            $this->getCacheKey(),
            true,
        );
    }

    protected function fetchItems(PaginationData $paginationData): array
    {
        $cache = $this->createCache();
        $this->cachedTotalCount = $cache->getTotalCount();

        return $cache->getEntities(
            $paginationData->currentPage,
            $paginationData->recordsPerPage,
        );
    }

    protected function getTotalRecords(): int
    {
        if ($this->cachedTotalCount === null) {
            throw new LogicException('fetchItems() must be called before getTotalRecords()');
        }

        return $this->cachedTotalCount;
    }

    // ─── Pagination ───────────────────────────────────────────

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
            $target->builder,
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