<?php

declare(strict_types=1);

class CategoryDeleteController extends AbstractDeleteController
{
    public function __construct(
        private CategoryDeleteService $deleteService,
    ) {
    }

    protected function getDeleteService(): AbstractDeleteService
    {
        return $this->deleteService;
    }

    protected function getLabel(): string
    {
        return DeletionLabel::CATEGORY->value;
    }

    protected function resolveRedirectUrl(): string
    {
        return '/admin/category-list/index';
    }
}