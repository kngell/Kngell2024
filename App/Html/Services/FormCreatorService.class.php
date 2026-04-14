<?php

declare(strict_types=1);
class FormCreatorService extends AbstractFormCreator
{
    public function __construct(
        private FormFactoryRegistry $factoryRegistry,
    ) {
    }

    public function create(string $action): ?FormTemplateInterface
    {
        return $this->createForActionAndRoute($action);
    }

    public function createForActionAndRoute(string $action, string $route = ''): ?FormTemplateInterface
    {
        $factory = $this->factoryRegistry->getFactory($action, $route);

        if (!$factory) {
            return null;
        }

        return $factory->createForm();
    }

    public function createForRoute(string $route): ?FormTemplateInterface
    {
        $action = $this->extractActionFromRoute($route);
        return $this->createForActionAndRoute($action, $route);
    }

    public function getSupportedActions(): array
    {
        return $this->factoryRegistry->getAllSupportedActions();
    }

    private function extractActionFromRoute(string $route): string
    {
        $parts = explode('/', trim($route, '/'));
        return end($parts) ?: '';
    }
}