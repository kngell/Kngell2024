<?php

declare(strict_types=1);

class ProductListDecorator extends AbstractHtmlDecorator
{
    public function __construct(
        Controller $controller,
        private PaginatedCacheFactory $factory,
        private ProductShowModel $model,
        private PaginationStateService $paginationService,
        private ObfuscatorManager $obfuscatorManager,
    ) {
        parent::__construct($controller);
    }

    public function page(): array
    {
        $target = $this->getTarget();
        if (!$target instanceof AdminController) {
            throw new HtmlDecoratorException(
                sprintf(
                    '%s requires EcommerceController, got %s',
                    self::class,
                    get_class($target),
                ),
            );
        }
        $productCache = $this->factory->createProductCache($this->model);
        $paginationData = $this->paginationService->getPaginationData($target->request);

        $totalRecords = $productCache->getTotalCount();
        $products = $productCache->getEntities(
            $paginationData->currentPage,
            $paginationData->recordsPerPage,
        );

        $totalPages = (int) ceil($totalRecords / $paginationData->recordsPerPage);

        $productList = new ProductListTable(
            $products,
            $target->builder,
            new IconBuilder(),
            new FileContentManager(),
            new TypePresenterFactory(
                $target->translator,
                $target->region,
                $this->obfuscatorManager,
            ),
            $target->flash,
        );

        $pagination = new Pagination(
            $target->builder,
            new IconBuilder(),
            $paginationData->currentPage,
            $paginationData->recordsPerPage,
            $totalRecords,
            $totalPages,
            $target->request,
            $paginationData->allowedPageSizes,
        );
        return parent::page() + [
            'productTable' => $productList->getTable(),
            'productTablePagination' => $pagination->getPagination(),
        ];
    }
}