<?php

declare(strict_types=1);

class ProductFormCreator extends AbstractFormCreator
{
    /** @var FormFactoryInterface[] */
    private array $factories;

    public function __construct(FormFactoryInterface ...$Factories)
    {
        $this->factories = $Factories;
    }

    public function create(string $action): ?FormTemplateInterface
    {
        return $this->createForActionAndRoute($action);
    }

    public function createForActionAndRoute(string $action, string $route = ''): ?FormTemplateInterface
    {
        foreach ($this->factories as $factory) {
            if ($factory->supports($action, $route)) {
                return $factory->createForm();
            }
        }

        return null;
    }

    public function createForRoute(string $route): ?FormTemplateInterface
    {
        // Extract action from route if possible
        $action = $this->extractActionFromRoute($route);

        return $this->createForActionAndRoute($action, $route);
    }

    /**
     * Get supported actions for debugging/UI purposes.
     */
    public function getSupportedActions(): array
    {
        $actions = [];
        foreach ($this->factories as $factory) {
            $reflection = new ReflectionClass($factory);
            $constants = $reflection->getConstants();

            if (isset($constants['SUPPORTED_ACTIONS'])) {
                $actions[get_class($factory)] = $constants['SUPPORTED_ACTIONS'];
            }
        }

        return $actions;
    }

    private function extractActionFromRoute(string $route): string
    {
        $parts = explode('/', trim($route, '/'));
        return end($parts) ?: '';
    }
}