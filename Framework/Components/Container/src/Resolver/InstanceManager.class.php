<?php

declare(strict_types=1);

/**
 * Manages singleton instances and rebound callbacks.
 * Extracted from Container to centralize instance lifecycle management.
 */
class InstanceManager
{
    /** @var array<string, mixed> */
    private array $instances = [];

    /** @var array<string, array<Closure>> */
    private array $reboundCallbacks = [];

    /** @var Container */
    private ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Check if an instance exists.
     */
    public function has(string $abstract): bool
    {
        return array_key_exists($abstract, $this->instances);
    }

    /**
     * Get an instance if it exists.
     */
    public function get(string $abstract): mixed
    {
        return $this->instances[$abstract] ?? null;
    }

    /**
     * Register an existing instance as shared.
     */
    public function set(string $abstract, mixed $instance): void
    {
        $this->instances[$abstract] = $instance;
        // Don't fire callbacks here - they should be fired after resolution is complete
    }

    /**
     * Remove an instance.
     */
    public function remove(string $abstract): void
    {
        unset($this->instances[$abstract]);
    }

    /**
     * Clear all instances.
     */
    public function clear(): void
    {
        $this->instances = [];
        $this->reboundCallbacks = [];
    }

    /**
     * Register a rebound callback.
     */
    public function onRebound(string $abstract, Closure $callback): void
    {
        if (!isset($this->reboundCallbacks[$abstract])) {
            $this->reboundCallbacks[$abstract] = [];
        }
        $this->reboundCallbacks[$abstract][] = $callback;
    }

    /**
     * Fire rebound callbacks for an abstract.
     */
    public function fireReboundCallbacks(string $abstract, mixed $instance): void
    {
        $callbacks = $this->reboundCallbacks[$abstract] ?? [];
        foreach ($callbacks as $callback) {
            $callback($this->container, $instance);
        }
    }

    /**
     * Drop stale instance when rebinding.
     */
    public function dropStaleInstance(string $abstract): void
    {
        unset($this->instances[$abstract]);
    }
}