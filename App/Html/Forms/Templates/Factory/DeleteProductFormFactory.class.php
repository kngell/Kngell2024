<?php

declare(strict_types=1);

class DeleteProductFormFactory implements FormFactoryInterface
{
    private const array SUPPORTED_ACTIONS = ['delete', 'destroy', 'remove'];
    private const array SUPPORTED_ROUTES = [
        'products/delete',
        'products/destroy',
        'products/remove',
        'product/delete',
        'admin/products/delete',
    ];

    public function __construct(
        private ProductFormConfirmation $confirmationForm,
    ) {
    }

    public function supports(string $action, string $route = ''): bool
    {
        // Check by action
        if (in_array($action, self::SUPPORTED_ACTIONS)) {
            return true;
        }

        // Check by route pattern
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
        return $this->confirmationForm;
    }
}