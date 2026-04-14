<?php

declare(strict_types=1);

/**
 * Improved Dependency Injection Container.
 *
 * Features:
 * - Automatic dependency resolution
 * - Circular dependency detection
 * - Support for any parameter type (scalar, object, array, etc.)
 * - Contextual binding
 * - Tagged services
 * - Method injection
 * - Performance optimizations
 */
class Container implements ContainerInterface
{
    /** @var ResolutionContext */
    protected ResolutionContext $resolutionContext;

    /** @var array<string, mixed> */
    protected array $globalParameters = [];

    /** @var bool */
    protected bool $autoWiring = true;

    /** @var DependencyResolver */
    private DependencyResolver $dependencyResolver;

    /** @var CallableExecutor */
    private CallableExecutor $callableExecutor;

    /** @var InstanceManager */
    private InstanceManager $instanceManager;

    /** @var BindingRegistry */
    private BindingRegistry $bindingRegistry;

    /** @var ServiceTagManager */
    private ServiceTagManager $tagManager;

    /** @var ClassBuilder */
    private ClassBuilder $classBuilder;

    /** @var ContextualBindingManager */
    private ContextualBindingManager $contextualManager;

    /** @var ContainerInterface */
    protected static ?ContainerInterface $instance = null;

    public function __construct()
    {
        $this->resolutionContext = new ResolutionContext();
        $this->bindingRegistry = new BindingRegistry($this);
        $this->instanceManager = new InstanceManager($this);
        $this->dependencyResolver = new DependencyResolver($this);
        $this->callableExecutor = new CallableExecutor($this->dependencyResolver, $this);
        $this->tagManager = new ServiceTagManager($this, $this->dependencyResolver);
        $this->classBuilder = new ClassBuilder($this, $this->dependencyResolver);
        $this->contextualManager = new ContextualBindingManager($this);
        $this->registerCoreBindings();
    }

    /**
     * Bind an abstract to a concrete implementation.
     */
    public function bind(
        string $abstract,
        Closure|string|array|null $concrete = null,
        bool $shared = false,
        mixed $parameters = [],
    ): self {
        $this->instanceManager->dropStaleInstance($abstract);

        $parameters = is_array($parameters) ? $parameters : [$parameters];

        $this->bindingRegistry->register(
            abstract: $abstract,
            concrete: $concrete,
            shared: $shared,
            parameters: $parameters,
        );

        return $this;
    }

    /**
     * Register a singleton binding.
     */
    public function singleton(
        string $abstract,
        Closure|string|null $concrete = null,
        bool $shared = true,
        mixed $args = [],
    ): self {
        return $this->bind($abstract, $concrete, true, $args);
    }

    /**
     * Register a lazy singleton service.
     */
    public function lazy(string $abstract, Closure|string|null $concrete = null): self
    {
        return $this->factory($abstract, function ($container) use ($concrete, $abstract) {
            static $instance = null;

            if ($instance === null) {
                if ($concrete instanceof Closure) {
                    $instance = $concrete($container);
                } elseif (is_string($concrete)) {
                    $instance = $container->resolve($concrete);
                } else {
                    $instance = $container->resolve($abstract);
                }
            }

            return $instance;
        });
    }

    /**
     * Bind with factory method.
     */
    public function factory(string $abstract, Closure $factory): self
    {
        $this->instanceManager->dropStaleInstance($abstract);

        $this->bindingRegistry->register(
            abstract: $abstract,
            concrete: $abstract,
            shared: false,
            factory: $factory,
        );

        return $this;
    }

    /**
     * Bind with tags for service location.
     */
    public function bindWithTags(
        string $abstract,
        Closure|string|null $concrete = null,
        array $tags = [],
        bool $shared = false,
    ): self {
        $this->bind($abstract, $concrete, $shared);

        if (!empty($tags)) {
            $this->tag($abstract, $tags);
        }

        return $this;
    }

    /**
     * Resolve an instance from the container.
     */
    public function get(string $id, mixed $args = []): mixed
    {
        return $this->resolve($id, $args);
    }

    /**
     * Make an instance (alias for get for backward compatibility).
     */
    public function make(string $abstract, array $parameters = []): mixed
    {
        return $this->resolve($abstract, $parameters);
    }

    /**
     * Resolve an instance with full dependency injection.
     */
    public function resolve(string $abstract, array $parameters = []): mixed
    {
        $abstract = $this->bindingRegistry->resolveAlias($abstract);

        // Return existing singleton instance
        if ($this->instanceManager->has($abstract)) {
            return $this->instanceManager->get($abstract);
        }

        try {
            $this->resolutionContext->startResolving($abstract);

            $instance = $this->build($abstract, $parameters);

            // Store singleton instances
            if ($this->bindingRegistry->isShared($abstract)) {
                $this->instanceManager->set($abstract, $instance);
            }

            $this->resolutionContext->finishResolving($abstract);

            // Fire rebound callbacks for ALL instances
            $this->instanceManager->fireReboundCallbacks($abstract, $instance);

            return $instance;
        } catch (Throwable $e) {
            $this->resolutionContext->finishResolving($abstract);
            throw $e;
        }
    }

    /**
     * Bind parameters for a specific abstract.
     */
    public function bindParams(string $abstract, mixed $args, ?string $argName = null): self
    {
        $abstract = $this->bindingRegistry->resolveAlias($abstract);

        if ($this->bindingRegistry->has($abstract)) {
            $parameters = $this->bindingRegistry->getParameters($abstract);

            if ($argName !== null) {
                $parameters[$argName] = $args;
            } else {
                $parameters = is_array($args) ? $args : [$args];
            }

            $this->bindingRegistry->updateParameters($abstract, $parameters);
        } else {
            $parameters = $argName !== null ? [$argName => $args] : (is_array($args) ? $args : [$args]);
            $this->bind($abstract, null, false, $parameters);
        }

        return $this;
    }

    /**
     * Check if the container has a binding or instance.
     */
    public function has(string $id): bool
    {
        $id = $this->bindingRegistry->resolveAlias($id);

        return $this->instanceManager->has($id) ||
               $this->bindingRegistry->has($id) ||
               $this->canAutoWire($id);
    }

    /**
     * Register an existing instance as shared in the container.
     */
    public function instance(string $abstract, mixed $instance): mixed
    {
        $this->instanceManager->dropStaleInstance($abstract);
        $this->instanceManager->set($abstract, $instance);

        // Create a binding for the instance
        if (is_object($instance)) {
            $this->bindingRegistry->registerInstance($abstract, $instance);
        } else {
            $this->bindingRegistry->register(
                abstract: $abstract,
                concrete: $instance,
                shared: true,
            );
        }

        // Fire rebound callbacks for the registered instance
        $this->instanceManager->fireReboundCallbacks($abstract, $instance);

        return $instance;
    }

    /**
     * Determine if the given abstract type has been bound.
     */
    public function isBound(string $abstract): bool
    {
        $abstract = $this->bindingRegistry->resolveAlias($abstract);

        return $this->bindingRegistry->has($abstract) ||
               $this->instanceManager->has($abstract);
    }

    /**
     * Remove a binding from the container.
     */
    public function remove(string $abstract): bool
    {
        $abstract = $this->bindingRegistry->resolveAlias($abstract);

        $removed = false;

        if ($this->bindingRegistry->remove($abstract)) {
            $removed = true;
        }

        if ($this->instanceManager->has($abstract)) {
            $this->instanceManager->remove($abstract);
            $removed = true;
        }

        // Remove from tags
        $this->tagManager->removeFromTags($abstract);

        // Remove contextual bindings
        $this->contextualManager->removeContextualBindingsFor($abstract);
        $this->contextualManager->removeMethodBindingsFor($abstract);

        return $removed;
    }

    /**
     * Flush all bindings and instances.
     */
    public function flush(): void
    {
        $this->bindingRegistry->clear();
        $this->tagManager->clear();
        $this->contextualManager->clear();
        $this->globalParameters = [];
        $this->resolutionContext->clear();
        $this->instanceManager->clear();

        // Update dependency resolver
        $this->dependencyResolver->setGlobalParameters([]);
        $this->dependencyResolver->setAutoWiring($this->autoWiring);

        // Re-register core bindings
        $this->registerCoreBindings();
    }

    // =========================================
    // CONTEXTUAL BINDINGS
    // =========================================

    /**
     * Add a contextual binding.
     * When resolving $abstract for $concrete, use $implementation.
     */
    /**
     * Add a contextual binding.
     * When resolving $abstract for $concrete, use $implementation.
     */
    public function when(string $concrete): ContextualBindingBuilder
    {
        return new ContextualBindingBuilder($this->contextualManager, $concrete);
    }

    /**
     * Internal method to add contextual binding (used by ContextualBindingBuilder).
     */
    public function addContextualBinding(string $concrete, string $abstract, mixed $implementation): void
    {
        $this->contextualManager->addContextualBinding($concrete, $abstract, $implementation);
    }

    /**
     * Add a method binding.
     */
    public function bindMethod(string $concrete, string $method, array $bindings): self
    {
        $this->contextualManager->addMethodBinding($concrete, $method, $bindings);
        return $this;
    }

    /**
     * Call a method with method bindings applied.
     */
    public function callMethodBinding(object $instance, string $method, array $parameters = []): mixed
    {
        $parameters = $this->contextualManager->applyMethodBindings($instance, $method, $parameters);
        return $this->call([$instance, $method], $parameters);
    }

    // =========================================
    // ADVANCED FEATURES - TAGS & ALIASES
    // =========================================

    /**
     * Create an alias for an abstract type.
     */
    public function alias(string $abstract, string $alias): self
    {
        $this->bindingRegistry->alias($abstract, $alias);
        return $this;
    }

    /**
     * Tag services for group resolution.
     */
    public function tag(string $abstract, array|string $tags): self
    {
        $this->tagManager->tag($abstract, $tags);
        return $this;
    }

    /**
     * Resolve all services with a given tag (eager loading).
     */
    public function tagged(string $tag): array
    {
        return $this->tagManager->getTagged($tag);
    }

    /**
     * Get a lazy collection for a tag.
     */
    public function taggedLazy(string $tag): LazyTagCollection
    {
        return $this->tagManager->getTaggedLazy($tag);
    }

    /**
     * Set global parameters available to all resolutions.
     */
    public function setGlobalParameters(array $parameters): self
    {
        $this->globalParameters = array_merge($this->globalParameters, $parameters);
        $this->dependencyResolver->setGlobalParameters($this->globalParameters);
        return $this;
    }

    /**
     * Enable or disable auto-wiring.
     */
    public function setAutoWiring(bool $enabled): self
    {
        $this->autoWiring = $enabled;
        $this->dependencyResolver->setAutoWiring($enabled);
        return $this;
    }

    /**
     * Call a method with dependency injection.
     */
    public function call(callable|array|string $callback, array $parameters = []): mixed
    {
        return $this->callableExecutor->call($callback, $parameters);
    }

    /**
     * Register a rebound callback.
     */
    public function rebinding(string $abstract, Closure $callback): self
    {
        $this->instanceManager->onRebound($abstract, $callback);
        return $this;
    }

    /**
     * Get the contextual binding manager (for ContextualBindingBuilder).
     */
    public function getContextualManager(): ContextualBindingManager
    {
        return $this->contextualManager;
    }

    /**
     * Register core container bindings.
     */
    protected function registerCoreBindings(): void
    {
        $this->instance(ContainerInterface::class, $this);
        $this->instance(self::class, $this);
    }

    /**
     * Build an instance of the given type.
     */
    protected function build(string $abstract, array $parameters = []): mixed
    {
        // Handle factory bindings
        if ($this->bindingRegistry->hasFactory($abstract)) {
            $factory = $this->bindingRegistry->getFactory($abstract);
            return $factory->call($this, $this, $parameters);
        }

        // Handle closure bindings
        if ($this->bindingRegistry->isClosure($abstract)) {
            $concrete = $this->bindingRegistry->getConcrete($abstract);
            return $concrete->call($this, $this, $parameters);
        }

        // Get concrete class to instantiate
        $concrete = $this->bindingRegistry->getConcrete($abstract);

        // Handle string/primitive values or already instantiated objects
        if (!is_string($concrete) || !class_exists($concrete)) {
            $parameters = $this->bindingRegistry->getParameters($abstract);
            if (!empty($parameters)) {
                return $parameters[0] ?? $concrete;
            }
            return $concrete;
        }

        // Delegate class instantiation to ClassBuilder
        return $this->classBuilder->build($concrete, $parameters, $this->bindingRegistry->get($abstract));
    }

    // =====================================================
    // HELPER METHODS
    // =====================================================

    /**
     * Check if a type can be auto-wired.
     */
    protected function canAutoWire(string $abstract): bool
    {
        if (!$this->autoWiring) {
            return false;
        }

        return $this->classBuilder->canAutoWire($abstract);
    }

    public static function setInstance(?self $container = null): ?ContainerInterface
    {
        return static::$instance = $container;
    }

    /**
     * Get the global container instance.
     */
    public static function getInstance(): ContainerInterface
    {
        if (static::$instance === null) {
            static::$instance = new static();
        }

        return static::$instance;
    }
}