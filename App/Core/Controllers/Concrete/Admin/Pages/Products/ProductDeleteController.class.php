<?php

declare(strict_types=1);

class ProductDeleteController extends AbstractDeleteController
{
    public function __construct(
        private ProductDeleteService $deleteService,
    ) {
    }

    protected function getDeleteService(): AbstractDeleteService
    {
        return $this->deleteService;
    }

    protected function getLabel(): string
    {
        return DeletionLabel::PRODUCT->value;
    }

    protected function resolveRedirectUrl(): string
    {
        return '/admin/admin/product-list';
    }
}