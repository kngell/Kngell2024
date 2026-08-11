<?php

declare(strict_types=1);
final class CategoryListController extends Controller
{
    public function __construct(
        private readonly CategoryTableConfigFactory $tableFactory,
        private readonly CategoryModel $model,
    ) {
        $this->layout(NavbarType::ADMIN);
    }

    public function index(): string
    {
        $this->pageTitle('Category List');

        return $this->cachePage(
            function () {
                $list = $this->decorate(
                    ListDecorator::class,
                    $this,
                    [
                        'factory' => $this->tableFactory,
                        'adapter' => new CategoryPaginatedAdapter($this->model),
                    ],
                );

                return $this->render('/table-list', $list->page());
            },
            ttl: 3600, // 1 hour
        );
    }
}