<?php

declare(strict_types=1);
class FormFactoryRegistry
{
    /** @var array<string, array{class: string, actions: array, routes: array}> */
    private array $factoryMetadata = [];

    /** @var array<string, FormFactoryInterface> */
    private array $resolvedFactories = [];

    private ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->registerFactoriesFromConstants();
    }

    public function findFactoryClassForActionAndRoute(string $action, string $route = ''): ?string
    {
        if (!empty($route)) {
            foreach ($this->factoryMetadata as $metadata) {
                foreach ($metadata['routes'] as $pattern) {
                    if ($this->routeMatches($route, $pattern)) {
                        return $metadata['class'];
                    }
                }
            }
        }

        // Then try to match by action
        foreach ($this->factoryMetadata as $metadata) {
            if (in_array($action, $metadata['actions'], true)) {
                return $metadata['class'];
            }
            if (!empty($action)) {
                foreach ($metadata['routes'] as $pattern) {
                    if (str_contains($action, $pattern)) {
                        return $metadata['class'];
                    }
                }
            }
        }

        return null;
    }

    public function getFactory(string $action, string $route = ''): ?FormFactoryInterface
    {
        $factoryClass = $this->findFactoryClassForActionAndRoute($action, $route);

        if (!$factoryClass) {
            return null;
        }

        // Lazy instantiate only the factory we need
        if (!isset($this->resolvedFactories[$factoryClass])) {
            $this->resolvedFactories[$factoryClass] = $this->container->get($factoryClass);
        }

        return $this->resolvedFactories[$factoryClass];
    }

    public function getAllSupportedActions(): array
    {
        $result = [];
        foreach ($this->factoryMetadata as $key => $metadata) {
            $result[$key] = [
                'actions' => $metadata['actions'],
                'routes' => $metadata['routes'],
            ];
        }
        return $result;
    }

    private function registerFactoriesFromConstants(): void
    {
        $factoryClasses = [
            MainProductFormFactory::class,
            DeleteProductFormFactory::class,
            HeroFormFactory::class,
            SmallBannerFormFactory::class,
            CategoryFormFactory::class,
        ];

        foreach ($factoryClasses as $class) {
            try {
                $reflection = new ReflectionClass($class);

                $actions = $reflection->getConstant('SUPPORTED_ACTIONS') ?: [];
                $routes = $reflection->getConstant('SUPPORTED_ROUTES') ?: [];

                $key = $this->extractKeyFromClass($class);

                $this->factoryMetadata[$key] = [
                    'class' => $class,
                    'actions' => $actions,
                    'routes' => $routes,
                ];
            } catch (ReflectionException $e) {
                // Log error but continue
                error_log("Failed to read factory metadata for {$class}: " . $e->getMessage());
            }
        }
    }

    private function extractKeyFromClass(string $class): string
    {
        // Get short class name without namespace
        $parts = explode('\\', $class);
        $shortName = end($parts);

        // Remove 'FormFactory' suffix
        $shortName = str_replace(['FormFactory', 'Factory'], '', $shortName);

        return StringUtils::camelCaseToSnakeCase($shortName);
        // Convert CamelCase to snake_case
        // return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $shortName));
    }

    private function routeMatches(string $currentRoute, string $pattern): bool
    {
        // Remove leading/trailing slashes for comparison
        $currentRoute = trim($currentRoute, '/');
        $pattern = trim($pattern, '/');

        // Direct match
        if ($currentRoute === $pattern) {
            return true;
        }

        if (str_contains($currentRoute, $pattern)) {
            return true;
        }

        // Pattern with wildcard support (if you want to add it)
        if (str_contains($pattern, '*')) {
            $pattern = preg_quote($pattern, '#');
            $pattern = str_replace('\*', '.*', $pattern);
            return (bool) preg_match('#^' . $pattern . '$#', $currentRoute);
        }

        return false;
    }
}