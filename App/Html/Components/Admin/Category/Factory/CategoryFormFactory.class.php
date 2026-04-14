<?php

declare(strict_types=1);

class CategoryFormFactory extends AbstractFormFactory
{
    private const array SUPPORTED_ROUTES = [
        'category/save',
        'category/edit',
        'category/add',
        'category-save/index',
    ];

    public function __construct(
        private CategoryForm $categoryForm,
    ) {
    }

    protected function getSupportedRoutes(): array
    {
        return self::SUPPORTED_ROUTES;
    }

    protected function getForm(): FormTemplateInterface
    {
        return $this->categoryForm;
    }
}