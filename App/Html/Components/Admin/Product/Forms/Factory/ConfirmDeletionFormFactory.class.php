<?php

declare(strict_types=1);

class ConfirmDeletionFormFactory implements FormFactoryInterface
{
    private const array SUPPORTED_ACTIONS = ['delete', 'destroy', 'remove'];
    private const array SUPPORTED_ROUTES = [
        'product-delete/delete',
        'hero-section-delete/delete',
    ];

    public function __construct(
        private ConfirmDeletionForm $confirmationForm,
    ) {
    }

    public function supports(string $action, string $route = ''): bool
    {
        // Check by action
        if (in_array($action, self::SUPPORTED_ACTIONS)) {
            return true;
        }

        // Check by route pattern
        if (!empty($action)) {
            foreach (self::SUPPORTED_ROUTES as $supportedRoute) {
                if (str_contains($action, $supportedRoute)) {
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