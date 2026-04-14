<?php

declare(strict_types=1);

/**
 * Handles calling methods/functions with dependency injection.
 * Supports #[Inject] attributes for explicit dependency control.
 */
class CallableExecutor
{
    private DependencyResolver $resolver;
    private Container $container;

    public function __construct(DependencyResolver $resolver, Container $container)
    {
        $this->resolver = $resolver;
        $this->container = $container;
    }

    /**
     * Call a method with dependency injection.
     */
    public function call(callable|array|string $callback, array $parameters = []): mixed
    {
        $normalizedCallback = $this->normalizeCallback($callback);
        return $this->callWithDependencies($normalizedCallback, $parameters);
    }

    /**
     * Normalize various callback formats to a callable array or function.
     */
    private function normalizeCallback(callable|array|string $callback): array|callable
    {
        // Handle string format "ClassName@method"
        if (is_string($callback) && str_contains($callback, '@')) {
            [$class, $method] = explode('@', $callback, 2);
            $object = $this->container->resolve($class);
            return [$object, $method];
        }

        // Handle array format with string class name - NEEDS TO RESOLVE THE CLASS TO OBJECT
        if (is_array($callback) && isset($callback[0]) && is_string($callback[0])) {
            // Resolve the class name to an object instance
            $callback[0] = $this->container->resolve($callback[0]);
            return $callback;
        }

        // If it's a string but not a function name, check if it's a class with __invoke
        if (is_string($callback) && !function_exists($callback) && class_exists($callback)) {
            $object = $this->container->resolve($callback);
            return [$object, '__invoke'];
        }

        return $callback;
    }

    /**
     * Call a function/method with dependency injection.
     */
    private function callWithDependencies(mixed $callback, array $parameters = []): mixed
    {
        // Make sure it's callable - if it's an array, check if first element is object
        if (is_array($callback)) {
            if (!isset($callback[0]) || !isset($callback[1])) {
                throw new InvalidArgumentException('Invalid callback array format');
            }
            if (!is_object($callback[0])) {
                throw new InvalidArgumentException(
                    sprintf('Callback object must be an object instance, got: %s', gettype($callback[0])),
                );
            }
            if (!is_callable($callback)) {
                throw new InvalidArgumentException(
                    sprintf('Method "%s" does not exist on object of type "%s"', $callback[1], get_class($callback[0])),
                );
            }
        } elseif (!is_callable($callback)) {
            throw new InvalidArgumentException(
                sprintf('Callback must be callable. Got: %s', is_object($callback) ? get_class($callback) : gettype($callback)),
            );
        }

        $reflector = $this->getCallReflector($callback);

        // Get explicit bindings from Inject attributes
        $injectBindings = $this->getInjectBindings($reflector);

        // Merge: explicit parameters > inject bindings > normal resolution
        $allParameters = array_merge($parameters, $injectBindings);

        $resolvedValues = $this->resolver->resolveMethodDependencies($reflector, $allParameters);

        $args = [];
        foreach ($reflector->getParameters() as $index => $parameter) {
            $resolvedValue = $resolvedValues[$index];

            if ($parameter->isVariadic() && is_array($resolvedValue)) {
                $args = array_merge($args, $resolvedValue);
            } else {
                $args[] = $resolvedValue;
            }
        }

        if ($reflector instanceof ReflectionMethod && !$reflector->isPublic()) {
            return $reflector->invokeArgs($callback[0], $args);
        }

        return call_user_func_array($callback, $args);
    }

    /**
     * Get explicit bindings from Inject attributes.
     *
     * @param ReflectionFunctionAbstract $reflector
     *
     * @return array<string, mixed>
     */
    private function getInjectBindings(ReflectionFunctionAbstract $reflector): array
    {
        $bindings = [];

        // Only methods support Inject attributes
        if (!$reflector instanceof ReflectionMethod) {
            return $bindings;
        }

        // Check if method has Inject attribute (enables DI for all parameters)
        $methodAttributes = $reflector->getAttributes(Inject::class);
        $methodHasInject = !empty($methodAttributes);

        foreach ($reflector->getParameters() as $parameter) {
            $paramName = $parameter->getName();

            // 1. Parameter-level Inject attribute takes precedence
            $paramAttributes = $parameter->getAttributes(Inject::class);
            if (!empty($paramAttributes)) {
                /** @var Inject $injectAttr */
                $injectAttr = $paramAttributes[0]->newInstance();
                $bindingId = $injectAttr->getId();

                if ($bindingId !== null) {
                    $bindings[$paramName] = $this->container->get($bindingId);
                }
                continue;
            }

            // 2. If method has Inject attribute, try to resolve by type with special cases
            if ($methodHasInject) {
                $type = $parameter->getType();
                if ($type instanceof ReflectionNamedType && !$type->isBuiltin()) {
                    $typeName = $type->getName();

                    // Special case: User type resolves to 'current.user' binding
                    if ($typeName === 'User' || $typeName === '\\User') {
                        if ($this->container->has('current.user')) {
                            $bindings[$paramName] = $this->container->get('current.user');
                        }
                    } else {
                        // Try to resolve by type
                        try {
                            if ($this->container->has($typeName)) {
                                $bindings[$paramName] = $this->container->get($typeName);
                            }
                        } catch (Throwable) {
                            // Let normal resolution handle it
                        }
                    }
                }
            }
        }

        return $bindings;
    }

    /**
     * Get reflection for a callable.
     */
    private function getCallReflector(callable|array $callback): ReflectionFunctionAbstract
    {
        if (is_array($callback)) {
            // Ensure we have an object instance
            if (!is_object($callback[0])) {
                throw new InvalidArgumentException('Callback array must contain an object instance');
            }
            return new ReflectionMethod($callback[0], $callback[1]);
        }
        return new ReflectionFunction($callback);
    }
}