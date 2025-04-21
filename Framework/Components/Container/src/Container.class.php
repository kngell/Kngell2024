<?php

declare(strict_types=1);

class Container implements ContainerInterface
{
    /** @var ContainerInterface */
    protected static $instance;

    /** @var array */
    protected array $services = [];

    /** @var array */
    protected array $bindings = [];

    /**
     * All of the registered rebound callbacks.
     *
     * @var array
     */
    protected $reboundCallbacks = [];

    private function __construct()
    {
    }

    /**
     * @param string $abstract
     * @param Closure|string|null $concrete
     * @param bool $shared
     * @param mixed $parameters
     * @return ContainerInterface
     */
    public function bind(string $abstract, Closure | string | null $concrete = null, bool $shared = false, mixed $parameters = []): ContainerInterface
    {
        $this->bindings[$abstract] = [
            'concrete' => $concrete !== null ? $concrete : $abstract,
            'shared' => $shared,
            'parameters' => $parameters,
        ];
        return $this;
    }

    /**
     * @param string $abstract
     * @param Closure|string|null $concrete
     * @param mixed $args
     * @return ContainerInterface
     */
    public function singleton(string $abstract, Closure | string | null $concrete = null, bool $shared = true, mixed $args = []): ContainerInterface
    {
        return $this->bind($abstract, $concrete, $shared, $args);
    }

    /**
     * @param string $id
     * @param mixed $args
     * @return mixed
     * @throws BindingResolutionException
     * @throws ReflectionException
     * @throws DependencyHasNoDefaultValueException
     */
    public function get(string $id, mixed $args = []) : mixed
    {
        if ($this->has($id)) {
            return $this->services[$id];
        }
        return $this->make($id, $args);
    }

    /**
     * @param string $abstract
     * @param mixed $args
     * @param null|string $argName
     * @return ContainerInterface
     */
    public function bindParams(string $abstract, mixed $args, ?string $argName = null) : ContainerInterface
    {
        if ($this->isBound($abstract)) {
            $this->bindings[$abstract]['parameters'] = $args;
        } else {
            $this->bind($abstract, null, false, []);
            null !== $argName ? $this->bindings[$abstract]['parameters'][$argName] = $args : $this->bindings[$abstract]['parameters'] = $args;
        }
        return $this;
    }

    public static function setInstance(?self $container = null)
    {
        return static::$instance = $container;
    }

    /**
     * Check is Container as singleton
     *  ==============================================.
     * @param string $id Identifier of the entry to look for.
     * @return bool
     */
    public function has(string $id): bool
    {
        return array_key_exists($id, $this->services) && isset($this->services[$id]);
    }

    /**
     * Register an existing instance as shared in the container.
     *
     * @param  string  $abstract
     * @param  mixed  $instance
     * @return mixed
     */
    public function instance(string $abstract, mixed $instance) : mixed
    {
        $isBound = $this->isBound($abstract);
        $this->services[$abstract] = $instance;
        if ($isBound) {
            $this->rebound($abstract);
        }
        return $instance;
    }

    /**
     * Determine if the given abstract type has been bound.
     * ========================================================.
     * @param  string  $abstract
     * @return bool
     */
    public function isBound($abstract)
    {
        return isset($this->bindings[$abstract]) ||
               isset($this->services[$abstract]);
    }

    public function remove(string $abstract) : bool
    {
        if ($this->isBound($abstract)) {
            unset($this->bindings[$abstract]);
            return true;
        }
        return false;
    }

    public function flush(): void
    {
        $this->bindings = [];
        $this->services = [];
    }

    /**
     * Fire the "rebound" callbacks for the given abstract type.
     *
     * @param  string  $abstract
     * @return void
     */
    protected function rebound($abstract)
    {
        $instance = $this->get($abstract);
        foreach ($this->getReboundCallbacks($abstract) as $callback) {
            call_user_func($callback, $this, $instance);
        }
    }

    /**
     * Get the rebound callbacks for a given type.
     *
     * @param  string  $abstract
     * @return array
     */
    protected function getReboundCallbacks($abstract)
    {
        return $this->reboundCallbacks[$abstract] ?? [];
    }

    /**
     * @param string|array $abstract
     * @param mixed $args
     * @return mixed
     * @throws BindingResolutionException
     * @throws ReflectionException
     * @throws DependencyHasNoDefaultValueException
     */
    private function make(string|array $abstract, mixed $args = []) : mixed
    {
        if (! empty($this->bindings[$abstract]['parameters'])) {
            $args = $this->bindings[$abstract]['parameters'];
        }
        $concrete = $this->bindings[$abstract]['concrete'] ?? $abstract;
        if ($concrete instanceof Closure || $concrete === $abstract) {
            if ($concrete instanceof Closure) {
                return $concrete($this);
            }
            $object = $this->resolve($concrete, $args);
        } else {
            $object = $this->get($concrete, $args);
        }
        if (isset($this->bindings[$abstract]) && $this->bindings[$abstract]['shared']) {
            $this->services[$abstract] = $object;
        }
        return $object;
    }

    /**
     * @param Closure|string $concrete
     * @param array|Closure $args
     * @return mixed
     * @throws BindingResolutionException
     * @throws ReflectionException
     * @throws DependencyHasNoValueException
     * @throws DependencyHasNoDefaultValueException
     */
    private function resolve(Closure | string $concrete, mixed $args = []): mixed
    {
        if ($concrete instanceof Closure) {
            return $concrete($this);
        }
        try {
            /** @var ReflectionClass */
            $reflector = new ReflectionClass($concrete);
            $parentClass = $reflector->getInterfaceNames();
        } catch (ReflectionException $e) {
            throw new BindingResolutionException("Target class [$concrete] does not exist.", 0, $e->getCode());
        }
        if (! $reflector->isInstantiable() && ! $reflector->isEnum()) {
            throw new BindingResolutionException("Target [$concrete] is not instantiable.");
        }
        $constructor = $reflector->getConstructor();
        if ($constructor === null) {
            return $this->objectWithContainer($reflector->newInstance(), $reflector);
        }
        $classDependencies = $this->resolveClassDependencies(
            $constructor->getParameters(),
            $args,
            $reflector
        );
        return $this->objectWithContainer($reflector->newInstanceArgs($classDependencies), $reflector);
    }

    /**
     * @param array $parameters
     * @param array|Closure $args
     * @return array
     * @throws DependencyHasNoValueException
     * @throws BindingResolutionException
     * @throws ReflectionException
     * @throws DependencyHasNoDefaultValueException
     */
    private function resolveClassDependencies(array $parameters, mixed $args): array
    {
        $dependencies = [];
        /** @var ReflectionParameter $parameter */
        foreach ($parameters as $key => $parameter) {
            $type = $parameter->getType();
            $resolver = DependencyResolverFactory::create($type, $parameter);
            $resolvedDependencies = $resolver->resolve($key, $args);
            if (! $type->isBuiltin() && ! $resolvedDependencies) {
                $dependencies[] = $this->get($type->getName());
            } else {
                $dependencies[] = $resolvedDependencies;
            }
        }
        return $dependencies;
    }

    /**
     * Set Container into created object
     * ======================================================.
     * @param ReflectionClass $reflector
     * @param object $obj
     * @return void
     */
    private function objectWithContainer(Object $obj, $reflector)
    {
        if ($reflector->hasProperty('container')) {
            $reflectionContainer = $reflector->getProperty('container');
            $reflectionContainer->setAccessible(true);
            if (! $reflectionContainer->isInitialized($obj)) {
                $reflectionContainer->setValue($obj, $this);
            }
        }
        return $obj;
    }
}
