<?php

declare(strict_types=1);

/**
 * Manages contextual and method bindings.
 * Extracted from Container to centralize contextual resolution logic.
 */
class ContextualBindingManager
{
    /** @var array<string, array<string, mixed>> */
    private array $contextualBindings = [];

    /** @var array<string, array<string, mixed>> */
    private array $methodBindings = [];

    /** @var ContainerInterface */
    private ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Add a contextual binding.
     *
     * @param string $concrete The class that needs the dependency
     * @param string $abstract The abstraction/interface to bind
     * @param mixed $implementation The concrete implementation to use
     */
    public function addContextualBinding(string $concrete, string $abstract, mixed $implementation): void
    {
        if (!isset($this->contextualBindings[$concrete])) {
            $this->contextualBindings[$concrete] = [];
        }

        $this->contextualBindings[$concrete][$abstract] = $implementation;
    }

    /**
     * Get a contextual binding for a specific concrete class and abstract.
     */
    public function getContextualBinding(string $concrete, string $abstract): mixed
    {
        return $this->contextualBindings[$concrete][$abstract] ?? null;
    }

    /**
     * Check if a contextual binding exists.
     */
    public function hasContextualBinding(string $concrete, string $abstract): bool
    {
        return isset($this->contextualBindings[$concrete][$abstract]);
    }

    /**
     * Get all contextual bindings for a concrete class.
     */
    public function getContextualBindingsFor(string $concrete): array
    {
        return $this->contextualBindings[$concrete] ?? [];
    }

    /**
     * Remove contextual bindings for a concrete class.
     */
    public function removeContextualBindingsFor(string $concrete): void
    {
        unset($this->contextualBindings[$concrete]);
    }

    /**
     * Add a method binding.
     *
     * @param string $concrete The class that has the method
     * @param string $method The method name
     * @param array $bindings Parameter bindings for the method
     */
    public function addMethodBinding(string $concrete, string $method, array $bindings): void
    {
        if (!isset($this->methodBindings[$concrete])) {
            $this->methodBindings[$concrete] = [];
        }

        $this->methodBindings[$concrete][$method] = $bindings;
    }

    /**
     * Get method bindings for a specific class and method.
     */
    public function getMethodBindings(string $concrete, string $method): array
    {
        return $this->methodBindings[$concrete][$method] ?? [];
    }

    /**
     * Check if method bindings exist.
     */
    public function hasMethodBindings(string $concrete, string $method): bool
    {
        return isset($this->methodBindings[$concrete][$method]);
    }

    /**
     * Get all method bindings for a concrete class.
     */
    public function getMethodBindingsFor(string $concrete): array
    {
        return $this->methodBindings[$concrete] ?? [];
    }

    /**
     * Remove method bindings for a concrete class.
     */
    public function removeMethodBindingsFor(string $concrete): void
    {
        unset($this->methodBindings[$concrete]);
    }

    /**
     * Clear all contextual and method bindings.
     */
    public function clear(): void
    {
        $this->contextualBindings = [];
        $this->methodBindings = [];
    }

    /**
     * Resolve a dependency with contextual binding awareness.
     *
     * @param string $abstract The abstraction being resolved
     * @param string|null $concrete The class that needs the dependency (for context)
     */
    public function resolveContextual(string $abstract, ?string $concrete = null): mixed
    {
        if ($concrete !== null && $this->hasContextualBinding($concrete, $abstract)) {
            $implementation = $this->getContextualBinding($concrete, $abstract);

            // If implementation is a string (class name), resolve it
            if (is_string($implementation) && class_exists($implementation)) {
                return $this->container->resolve($implementation);
            }

            // If it's a Closure, call it
            if ($implementation instanceof Closure) {
                return $implementation($this->container);
            }

            // Return as-is (could be an instance or primitive)
            return $implementation;
        }

        return null;
    }

    /**
     * Apply method bindings when calling a method.
     *
     * @param object|string $concrete The class instance or class name
     * @param string $method The method being called
     * @param array $parameters Original parameters
     *
     * @return array Modified parameters with bindings applied
     */
    public function applyMethodBindings(object|string $concrete, string $method, array $parameters = []): array
    {
        // Get class name from object or use string directly
        $className = is_object($concrete) ? get_class($concrete) : $concrete;

        if (!$this->hasMethodBindings($className, $method)) {
            return $parameters;
        }

        $bindings = $this->getMethodBindings($className, $method);

        // Merge bindings with provided parameters (bindings take precedence)
        return array_merge($parameters, $bindings);
    }

    /**
     * Get all contextual bindings (for debugging/inspection).
     */
    public function getAllContextualBindings(): array
    {
        return $this->contextualBindings;
    }

    /**
     * Get all method bindings (for debugging/inspection).
     */
    public function getAllMethodBindings(): array
    {
        return $this->methodBindings;
    }
}