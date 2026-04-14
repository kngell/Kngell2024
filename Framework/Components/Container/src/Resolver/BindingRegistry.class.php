<?php

declare(strict_types=1);

/**
 * Manages bindings, aliases, and binding metadata.
 * Extracted from Container to centralize binding management.
 */
class BindingRegistry
{
    /** @var array<string, BindingDefinition> */
    private array $bindings = [];

    /** @var array<string, string> */
    private array $aliases = [];

    /** @var Container */
    private ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Register a binding.
     */
    public function register(
        string $abstract,
        Closure|string|array|null $concrete = null,
        bool $shared = false,
        array $parameters = [],
        ?Closure $factory = null,
    ): BindingDefinition {
        $concrete = $concrete ?? $abstract;

        $binding = new BindingDefinition(
            abstract: $abstract,
            concrete: $concrete,
            shared: $shared,
            parameters: $parameters,
            factory: $factory,
        );

        $this->bindings[$abstract] = $binding;

        return $binding;
    }

    /**
     * Register an existing object instance as a binding.
     * This is a special case for the instance() method.
     */
    public function registerInstance(string $abstract, object $instance, bool $shared = true): void
    {
        // For object instances, we store the object directly as concrete
        // but we don't type-hint it in the BindingDefinition constructor
        // Instead, we create a binding with the object as concrete value
        $binding = new BindingDefinition(
            abstract: $abstract,
            concrete: $instance,
            shared: $shared,
            parameters: [],
            factory: null,
        );

        $this->bindings[$abstract] = $binding;
    }

    /**
     * Check if an abstract has a binding.
     */
    public function has(string $abstract): bool
    {
        return isset($this->bindings[$abstract]);
    }

    /**
     * Get a binding by abstract.
     */
    public function get(string $abstract): ?BindingDefinition
    {
        return $this->bindings[$abstract] ?? null;
    }

    /**
     * Remove a binding.
     */
    public function remove(string $abstract): bool
    {
        if (isset($this->bindings[$abstract])) {
            unset($this->bindings[$abstract]);
            return true;
        }

        return false;
    }

    /**
     * Clear all bindings.
     */
    public function clear(): void
    {
        $this->bindings = [];
        $this->aliases = [];
    }

    /**
     * Register an alias for an abstract.
     */
    public function alias(string $abstract, string $alias): void
    {
        $this->aliases[$alias] = $abstract;
    }

    /**
     * Resolve an alias to its actual abstract.
     */
    public function resolveAlias(string $abstract): string
    {
        return $this->aliases[$abstract] ?? $abstract;
    }

    /**
     * Check if an alias exists.
     */
    public function hasAlias(string $alias): bool
    {
        return isset($this->aliases[$alias]);
    }

    /**
     * Get all bindings.
     */
    public function getAll(): array
    {
        return $this->bindings;
    }

    /**
     * Get all aliases.
     */
    public function getAllAliases(): array
    {
        return $this->aliases;
    }

    /**
     * Check if an abstract is shared (singleton).
     */
    public function isShared(string $abstract): bool
    {
        $binding = $this->get($abstract);
        return $binding?->isShared() ?? false;
    }

    /**
     * Update binding parameters.
     */
    public function updateParameters(string $abstract, array $parameters): void
    {
        if (isset($this->bindings[$abstract])) {
            $this->bindings[$abstract] = $this->bindings[$abstract]->withParameters($parameters);
        }
    }

    /**
     * Get concrete implementation for an abstract.
     */
    public function getConcrete(string $abstract): Closure|string|array|null
    {
        $binding = $this->get($abstract);
        return $binding ? $binding->getConcrete() : $abstract;
    }

    /**
     * Check if a binding has a factory.
     */
    public function hasFactory(string $abstract): bool
    {
        $binding = $this->get($abstract);
        return $binding?->hasFactory() ?? false;
    }

    /**
     * Get factory for a binding.
     */
    public function getFactory(string $abstract): ?Closure
    {
        $binding = $this->get($abstract);
        return $binding?->factory;
    }

    /**
     * Check if a binding is a closure.
     */
    public function isClosure(string $abstract): bool
    {
        $binding = $this->get($abstract);
        return $binding?->isClosure() ?? false;
    }

    /**
     * Get binding parameters.
     */
    public function getParameters(string $abstract): array
    {
        $binding = $this->get($abstract);
        return $binding?->parameters ?? [];
    }
}