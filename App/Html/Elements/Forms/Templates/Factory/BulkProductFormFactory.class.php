<?php

declare(strict_types=1);

class BulkProductFormFactory implements FormFactoryInterface
{
    private const array SUPPORTED_ACTIONS = ['bulk', 'batch', 'mass'];
    private const array SUPPORTED_ROUTES = [
        'products/bulk-edit',
        'products/batch-update',
        'admin/products/bulk',
    ];

    public function __construct(
        private BulkProductForm $bulkForm,
    ) {
    }

    public function supports(string $action, string $route = ''): bool
    {
        if (in_array($action, self::SUPPORTED_ACTIONS)) {
            return true;
        }

        if (!empty($route)) {
            foreach (self::SUPPORTED_ROUTES as $supportedRoute) {
                if (str_contains($route, $supportedRoute)) {
                    return true;
                }
            }
        }

        return false;
    }

    public function createForm(): FormTemplateInterface
    {
        return $this->bulkForm;
    }
}