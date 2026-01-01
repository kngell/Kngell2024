<?php

declare(strict_types=1);

class ProductListDecorator extends AbstractHtmlDecorator
{
    public function __construct(
        Controller $controller,
        private PaginationCachingFactory $factory,
        private ProductShowModel $model,
        private PaginationStateService $paginationService,
    ) {
        parent::__construct($controller);
    }

    public function page(): array
    {
        $productCache = $this->factory->create(ProductShow::class, $this->model);
        $paginationData = $this->paginationService->getPaginationData($this->request);

        $totalRecords = $productCache->getTotalCount();
        $products = $productCache->getEntities(
            $paginationData->currentPage,
            $paginationData->recordsPerPage,
        );

        $totalPages = (int) ceil($totalRecords / $paginationData->recordsPerPage);

        $productList = new ProductTable(
            $products,
            $this->builder,
            new IconBuilder(),
            new FileContentManager(),
            new TypePresenterFactory(
                $this->translator,
                $this->region,
            ),
        );

        $pagination = new Pagination(
            $this->builder,
            new IconBuilder(),
            $paginationData->currentPage,
            $paginationData->recordsPerPage,
            $totalRecords,
            $totalPages,
            $this->request,
            $paginationData->allowedPageSizes,
        );

        return array_merge(
            $this->controller->page(),
            [
                'productTable' => $productList->getTable(),
                'productTablePagination' => $pagination->getPagination(),
            ],
        );
    }
}