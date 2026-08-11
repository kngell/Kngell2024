<?php

declare(strict_types=1);

abstract class AbstractFormFactory implements FormFactoryInterface
{
    public function supports(string $action, string $route = ''): bool
    {
        if (!empty($action)) {
            foreach ($this->getSupportedRoutes() as $supportedRoute) {
                if (str_contains($action, $supportedRoute)) {
                    return true;
                }
            }
        }

        // Allow child classes to add additional checks
        // if ($this->additionalSupports($action, $route)) {
        //     return true;
        // }

        return false;
    }

    public function createForm(?formConfig $config = null): FormTemplateInterface
    {
        return $this->getForm($config);
    }

    abstract protected function getSupportedRoutes(): array;

    abstract protected function getForm(?formConfig $config = null): FormTemplateInterface;

    protected function additionalSupports(string $action, string $route = ''): bool
    {
        return false;
    }
}