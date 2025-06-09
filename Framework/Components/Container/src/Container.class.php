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
    /** @var ContainerInterface */
    protected static ?ContainerInterface $instance = null;

    /** @var array<string, BindingDefinition> */
    protected array $bindings = [];

    /** @var array<string, mixed> */
    protected array $instances = [];

    /** @var array<string, array> */
    protected array $aliases = [];

    /** @var array<string, array> */
    protected array $tags = [];

    /** @var array<string, array> */
    protected array $contextualBindings = [];

    /** @var array<string, array> */
    protected array $reboundCallbacks = [];

    /** @var array<string, array> */
    protected array $methodBindings = [];

    /** @var ResolutionContext */
    protected ResolutionContext $resolutionContext;

    /** @var array<string, mixed> */
    protected array $globalParameters = [];

    /** @var bool */
    protected bool $autoWiring = true;

    private function __construct()
    {
        $this->resolutionContext = new ResolutionContext();
        $this->registerCoreBindings();
    }

    /**
     * Bind an abstract to a concrete implementation.
     */
    public function bind(
        string $abstract,
        Closure|string|null $concrete = null,
        bool $shared = false,
        mixed $parameters = []
    ): self {
        $this->dropStaleInstances($abstract);

        $concrete = $concrete ?? $abstract;
        $parameters = is_array($parameters) ? $parameters : [$parameters];

        $this->bindings[$abstract] = new BindingDefinition(
            abstract: $abstract,
            concrete: $concrete,
            shared: $shared,
            parameters: $parameters
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
        mixed $args = []
    ): self {
        return $this->bind($abstract, $concrete, true, $args);
    }

    /**
     * Bind with factory method.
     */
    public function factory(string $abstract, Closure $factory): self
    {
        $this->dropStaleInstances($abstract);

        $this->bindings[$abstract] = new BindingDefinition(
            abstract: $abstract,
            concrete: $abstract,
            shared: false,
            factory: $factory
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
        bool $shared = false
    ): self {
        $this->bind($abstract, $concrete, $shared);

        if (! empty($tags)) {
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
        $abstract = $this->getAlias($abstract);

        // Return existing singleton instance
        if (array_key_exists($abstract, $this->instances)) {
            return $this->instances[$abstract];
        }

        try {
            $this->resolutionContext->startResolving($abstract);

            $instance = $this->build($abstract, $parameters);

            // Store singleton instances
            if ($this->isShared($abstract)) {
                $this->instances[$abstract] = $instance;
            }

            $this->resolutionContext->finishResolving($abstract);

            // Fire rebound callbacks
            $this->fireReboundCallbacks($abstract, $instance);

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
        if ($this->isBound($abstract)) {
            $binding = $this->bindings[$abstract];
            $parameters = $binding->parameters;

            if ($argName !== null) {
                $parameters[$argName] = $args;
            } else {
                $parameters = is_array($args) ? $args : [$args];
            }

            $this->bindings[$abstract] = $binding->withParameters($parameters);
        } else {
            $parameters = $argName !== null ? [$argName => $args] : (is_array($args) ? $args : [$args]);
            $this->bind($abstract, null, false, $parameters);
        }

        return $this;
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

    /**
     * Check if the container has a binding or instance.
     */
    public function has(string $id): bool
    {
        $id = $this->getAlias($id);

        return isset($this->instances[$id]) ||
               isset($this->bindings[$id]) ||
               $this->canAutoWire($id);
    }

    /**
     * Register an existing instance as shared in the container.
     */
    public function instance(string $abstract, mixed $instance): mixed
    {
        $this->dropStaleInstances($abstract);

        $this->instances[$abstract] = $instance;

        // Create a binding for the instance
        $this->bindings[$abstract] = new BindingDefinition(
            abstract: $abstract,
            concrete: $instance,
            shared: true
        );

        $this->fireReboundCallbacks($abstract, $instance);

        return $instance;
    }

    /**
     * Determine if the given abstract type has been bound.
     */
    public function isBound(string $abstract): bool
    {
        $abstract = $this->getAlias($abstract);

        return isset($this->bindings[$abstract]) ||
               isset($this->instances[$abstract]);
    }

    /**
     * Remove a binding from the container.
     */
    public function remove(string $abstract): bool
    {
        $abstract = $this->getAlias($abstract);

        $removed = false;

        if (isset($this->bindings[$abstract])) {
            unset($this->bindings[$abstract]);
            $removed = true;
        }

        if (isset($this->instances[$abstract])) {
            unset($this->instances[$abstract]);
            $removed = true;
        }

        return $removed;
    }

    /**
     * Flush all bindings and instances.
     */
    public function flush(): void
    {
        $this->bindings = [];
        $this->instances = [];
        $this->aliases = [];
        $this->tags = [];
        $this->contextualBindings = [];
        $this->reboundCallbacks = [];
        $this->methodBindings = [];
        $this->globalParameters = [];
        $this->resolutionContext->clear();

        // Re-register core bindings
        $this->registerCoreBindings();
    }

    // =========================================
    // NEW ADVANCED FEATURES
    // =========================================

    /**
     * Create an alias for an abstract type.
     */
    public function alias(string $abstract, string $alias): self
    {
        $this->aliases[$alias] = $abstract;
        return $this;
    }

    /**
     * Tag services for group resolution.
     */
    public function tag(string $abstract, array|string $tags): self
    {
        $tags = is_array($tags) ? $tags : [$tags];

        foreach ($tags as $tag) {
            if (! isset($this->tags[$tag])) {
                $this->tags[$tag] = [];
            }
            $this->tags[$tag][] = $abstract;
        }

        return $this;
    }

    /**
     * Resolve all services with a given tag.
     */
    public function tagged(string $tag): array
    {
        if (! isset($this->tags[$tag])) {
            return [];
        }

        $services = [];
        foreach ($this->tags[$tag] as $abstract) {
            $services[] = $this->resolve($abstract);
        }

        return $services;
    }

    /**
     * Set global parameters available to all resolutions.
     */
    public function setGlobalParameters(array $parameters): self
    {
        $this->globalParameters = array_merge($this->globalParameters, $parameters);
        return $this;
    }

    /**
     * Enable or disable auto-wiring.
     */
    public function setAutoWiring(bool $enabled): self
    {
        $this->autoWiring = $enabled;
        return $this;
    }

    /**
     * Call a method with dependency injection.
     */
    public function call(callable|array|string $callback, array $parameters = []): mixed
    {
        if (is_string($callback) && str_contains($callback, '@')) {
            [$class, $method] = explode('@', $callback, 2);
            $callback = [$this->resolve($class), $method];
        }

        if (is_array($callback)) {
            [$object, $method] = $callback;
            if (is_string($object)) {
                $object = $this->resolve($object);
            }
            $callback = [$object, $method];
        }

        return $this->callWithDependencies($callback, $parameters);
    }

    /**
     * Register a rebound callback.
     */
    public function rebinding(string $abstract, Closure $callback): self
    {
        $this->reboundCallbacks[$abstract][] = $callback;
        return $this;
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
        $binding = $this->getBinding($abstract);

        // Handle factory bindings
        if ($binding && $binding->hasFactory()) {
            return $binding->factory->call($this, $this, $parameters);
        }

        // Handle closure bindings
        if ($binding && $binding->isClosure()) {
            return $binding->concrete->call($this, $this, $parameters);
        }

        // Get concrete class to instantiate
        $concrete = $binding ? $binding->getConcrete() : $abstract;

        // Handle string/primitive values
        if (! is_string($concrete) || ! class_exists($concrete)) {
            if ($binding && ! empty($binding->parameters)) {
                return $binding->parameters[0] ?? $concrete;
            }
            return $concrete;
        }

        return $this->buildClass($concrete, $parameters, $binding);
    }

    /**
     * Build a class instance with dependency injection.
     */
    protected function buildClass(string $concrete, array $parameters = [], ?BindingDefinition $binding = null): object
    {
        try {
            $reflector = new ReflectionClass($concrete);
        } catch (ReflectionException $e) {
            throw ContainerException::cannotResolve($concrete, 'Class does not exist');
        }

        if (! $reflector->isInstantiable()) {
            throw ContainerException::cannotResolve($concrete, 'Class is not instantiable');
        }

        $constructor = $reflector->getConstructor();

        // No constructor - simple instantiation
        if ($constructor === null) {
            $instance = $reflector->newInstance();
            return $this->injectContainerIfNeeded($instance, $reflector);
        }

        // Resolve constructor dependencies
        $dependencies = $this->resolveConstructorDependencies(
            $constructor,
            $parameters,
            $binding
        );

        $instance = $reflector->newInstanceArgs($dependencies);
        return $this->injectContainerIfNeeded($instance, $reflector);
    }

    /**
     * Resolve constructor dependencies.
     */
    protected function resolveConstructorDependencies(
        ReflectionMethod $constructor,
        array $parameters = [],
        ?BindingDefinition $binding = null
    ): array {
        $dependencies = [];
        $bindingParameters = $binding?->parameters ?? [];
        $allParameters = array_merge($this->globalParameters, $bindingParameters, $parameters);

        foreach ($constructor->getParameters() as $parameter) {
            $dependency = $this->resolveDependency($parameter, $allParameters);
            $dependencies[] = $dependency;
        }

        return $dependencies;
    }

    /**
     * Resolve a single dependency parameter.
     */
    protected function resolveDependency(ReflectionParameter $parameter, array $parameters = []): mixed
    {
        $name = $parameter->getName();
        $type = $parameter->getType();

        // Check if parameter is provided explicitly
        if (array_key_exists($name, $parameters)) {
            return $parameters[$name];
        }

        // Check positional parameters
        if (isset($parameters[$parameter->getPosition()])) {
            return $parameters[$parameter->getPosition()];
        }

        // Handle typed parameters
        if ($type instanceof ReflectionNamedType) {
            return $this->resolveTypedDependency($parameter, $type);
        }

        // Handle union types
        if ($type instanceof ReflectionUnionType) {
            return $this->resolveUnionTypeDependency($parameter, $type);
        }

        // Handle intersection types (PHP 8.1+)
        if ($type instanceof ReflectionIntersectionType) {
            return $this->resolveIntersectionTypeDependency($parameter, $type);
        }

        // Try default value
        if ($parameter->isDefaultValueAvailable()) {
            return $parameter->getDefaultValue();
        }

        // Allow null if nullable
        if ($parameter->allowsNull()) {
            return null;
        }

        throw ContainerException::cannotResolve(
            $parameter->getDeclaringClass()?->getName() ?? 'unknown',
            "Cannot resolve parameter [{$name}]"
        );
    }

    /**
     * Resolve typed dependency (class, interface, or built-in type).
     */
    protected function resolveTypedDependency(ReflectionParameter $parameter, ReflectionNamedType $type): mixed
    {
        $typeName = $type->getName();

        // Handle built-in types
        if ($type->isBuiltin()) {
            return $this->resolveBuiltinType($parameter, $typeName);
        }

        // Handle class/interface types
        try {
            return $this->resolve($typeName);
        } catch (Throwable $e) {
            if ($parameter->isDefaultValueAvailable()) {
                return $parameter->getDefaultValue();
            }

            if ($parameter->allowsNull()) {
                return null;
            }

            throw $e;
        }
    }

    /**
     * Resolve built-in type parameters.
     */
    protected function resolveBuiltinType(ReflectionParameter $parameter, string $typeName): mixed
    {
        $name = $parameter->getName();

        // Check for specific parameter bindings
        if (isset($this->globalParameters[$name])) {
            return $this->castToType($this->globalParameters[$name], $typeName);
        }

        // Try default value
        if ($parameter->isDefaultValueAvailable()) {
            return $parameter->getDefaultValue();
        }

        // Handle nullable types
        if ($parameter->allowsNull()) {
            return null;
        }

        // Provide sensible defaults for common types
        return match ($typeName) {
            'string' => '',
            'int' => 0,
            'float' => 0.0,
            'bool' => false,
            'array' => [],
            default => throw ContainerException::cannotResolve(
                $parameter->getDeclaringClass()?->getName() ?? 'unknown',
                "Cannot resolve built-in type [{$typeName}] for parameter [{$name}]"
            )
        };
    }

    /**
     * Cast value to specific type.
     */
    protected function castToType(mixed $value, string $type): mixed
    {
        return match ($type) {
            'string' => (string) $value,
            'int' => (int) $value,
            'float' => (float) $value,
            'bool' => (bool) $value,
            'array' => (array) $value,
            default => $value
        };
    }

    /**
     * Resolve union type dependency.
     */
    protected function resolveUnionTypeDependency(ReflectionParameter $parameter, ReflectionUnionType $type): mixed
    {
        $types = $type->getTypes();

        foreach ($types as $unionType) {
            if ($unionType instanceof ReflectionNamedType) {
                try {
                    if ($unionType->isBuiltin()) {
                        return $this->resolveBuiltinType($parameter, $unionType->getName());
                    } else {
                        return $this->resolve($unionType->getName());
                    }
                } catch (Throwable) {
                    continue; // Try next type in union
                }
            }
        }

        if ($parameter->isDefaultValueAvailable()) {
            return $parameter->getDefaultValue();
        }

        if ($parameter->allowsNull()) {
            return null;
        }

        throw ContainerException::cannotResolve(
            $parameter->getDeclaringClass()?->getName() ?? 'unknown',
            "Cannot resolve union type for parameter [{$parameter->getName()}]"
        );
    }

    /**
     * Resolve intersection type dependency.
     */
    protected function resolveIntersectionTypeDependency(ReflectionParameter $parameter, ReflectionIntersectionType $type): mixed
    {
        // For intersection types, we need an object that implements all interfaces
        // This is complex and might need specific handling based on your use case
        $types = $type->getTypes();

        if (! empty($types) && $types[0] instanceof ReflectionNamedType) {
            try {
                return $this->resolve($types[0]->getName());
            } catch (Throwable) {
                // Fall through to default handling
            }
        }

        if ($parameter->isDefaultValueAvailable()) {
            return $parameter->getDefaultValue();
        }

        if ($parameter->allowsNull()) {
            return null;
        }

        throw ContainerException::cannotResolve(
            $parameter->getDeclaringClass()?->getName() ?? 'unknown',
            "Cannot resolve intersection type for parameter [{$parameter->getName()}]"
        );
    }

    // ============================================================================
    // HELPER METHODS
    // ============================================================================

    /**
     * Get the binding for an abstract type.
     */
    protected function getBinding(string $abstract): ?BindingDefinition
    {
        return $this->bindings[$abstract] ?? null;
    }

    /**
     * Get the alias for an abstract type.
     */
    protected function getAlias(string $abstract): string
    {
        return $this->aliases[$abstract] ?? $abstract;
    }

    /**
     * Check if an abstract type is shared (singleton).
     */
    protected function isShared(string $abstract): bool
    {
        $binding = $this->getBinding($abstract);
        return $binding?->isShared() ?? false;
    }

    /**
     * Check if a type can be auto-wired.
     */
    protected function canAutoWire(string $abstract): bool
    {
        if (! $this->autoWiring) {
            return false;
        }

        try {
            $reflector = new ReflectionClass($abstract);
            return $reflector->isInstantiable();
        } catch (ReflectionException) {
            return false;
        }
    }

    /**
     * Drop stale instances when rebinding.
     */
    protected function dropStaleInstances(string $abstract): void
    {
        unset($this->instances[$abstract]);
    }

    /**
     * Fire rebound callbacks.
     */
    protected function fireReboundCallbacks(string $abstract, mixed $instance): void
    {
        foreach ($this->getReboundCallbacks($abstract) as $callback) {
            $callback($this, $instance);
        }
    }

    /**
     * Get rebound callbacks for an abstract type.
     */
    protected function getReboundCallbacks(string $abstract): array
    {
        return $this->reboundCallbacks[$abstract] ?? [];
    }

    /**
     * Inject container into object if it has a container property.
     */
    protected function injectContainerIfNeeded(object $instance, ReflectionClass $reflector): object
    {
        if ($reflector->hasProperty('container')) {
            $property = $reflector->getProperty('container');
            $property->setAccessible(true);

            if (! $property->isInitialized($instance)) {
                $property->setValue($instance, $this);
            }
        }

        return $instance;
    }

    /**
     * Call a function/method with dependency injection.
     */
    protected function callWithDependencies(callable|array|string $callback, array $parameters = []): mixed
    {
        if (! is_array($callback) && ! is_callable($callback)) {
            throw new InvalidArgumentException('Callback must be callable or array.');
        }
        $reflector = $this->getCallReflector($callback);
        $dependencies = $this->resolveMethodDependencies($reflector, $parameters);
        // If it's a method and not public, use reflection to invoke
        if ($reflector instanceof ReflectionMethod && ! $reflector->isPublic()) {
            $reflector->setAccessible(true);
            return $reflector->invokeArgs($callback[0], $dependencies);
        }

        return call_user_func_array($callback, $dependencies);
    }

    /**
     * Get reflection for a callable.
     */
    protected function getCallReflector(callable|array $callback): ReflectionFunctionAbstract
    {
        if (! is_array($callback) && ! is_callable($callback)) {
            throw new InvalidArgumentException('Callback must be callable or array.');
        }

        if (is_array($callback)) {
            return new ReflectionMethod($callback[0], $callback[1]);
        }

        return new ReflectionFunction($callback);
    }

    /**
     * Resolve method dependencies.
     */
    protected function resolveMethodDependencies(ReflectionFunctionAbstract $reflector, array $parameters = []): array
    {
        $dependencies = [];

        foreach ($reflector->getParameters() as $parameter) {
            $dependency = $this->resolveDependency($parameter, $parameters);
            $dependencies[] = $dependency;
        }

        return $dependencies;
    }
}