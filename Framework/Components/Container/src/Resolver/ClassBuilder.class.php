<?php

declare(strict_types=1);

/**
 * Handles class instantiation with dependency injection.
 * Extracted from Container to centralize object creation logic.
 */
class ClassBuilder
{
    private ContainerInterface $container;
    private DependencyResolver $dependencyResolver;

    public function __construct(ContainerInterface $container, DependencyResolver $dependencyResolver)
    {
        $this->container = $container;
        $this->dependencyResolver = $dependencyResolver;
    }

    /**
     * Build a class instance with dependency injection.
     */
    public function build(string $concrete, array $parameters = [], ?BindingDefinition $binding = null): object
    {
        try {
            $reflector = CustomReflection::getInstance($concrete)->getClass();
        } catch (ReflectionException $e) {
            throw ContainerException::cannotResolve($concrete, 'Class does not exist');
        }

        if (!$reflector->isInstantiable()) {
            throw ContainerException::cannotResolve($concrete, 'Class is not instantiable');
        }

        $constructor = $reflector->getConstructor();

        // No constructor - simple instantiation
        if ($constructor === null) {
            $instance = $reflector->newInstance();
            return $this->injectContainerIfNeeded($instance, $reflector);
        }

        // Resolve constructor dependencies
        $resolvedValues = $this->dependencyResolver->resolveConstructorDependencies(
            $constructor,
            $parameters,
            $binding,
        );

        // Prepare arguments, handling variadic parameters
        $args = $this->prepareConstructorArguments($constructor, $resolvedValues);

        $instance = $reflector->newInstanceArgs($args);
        return $this->injectContainerIfNeeded($instance, $reflector);
    }

    /**
     * Check if a class can be auto-wired (instantiable).
     */
    public function canAutoWire(string $class): bool
    {
        try {
            $reflector = new ReflectionClass($class);
            return $reflector->isInstantiable();
        } catch (ReflectionException) {
            return false;
        }
    }

    /**
     * Get constructor parameters for a class (for debugging/inspection).
     */
    public function getConstructorParameters(string $class): array
    {
        try {
            $reflector = new ReflectionClass($class);
            $constructor = $reflector->getConstructor();

            if ($constructor === null) {
                return [];
            }

            $parameters = [];
            foreach ($constructor->getParameters() as $parameter) {
                $type = $parameter->getType();
                $parameters[] = [
                    'name' => $parameter->getName(),
                    'type' => $type instanceof ReflectionNamedType ? $type->getName() : (string) $type,
                    'optional' => $parameter->isOptional(),
                    'variadic' => $parameter->isVariadic(),
                    'allowsNull' => $parameter->allowsNull(),
                ];
            }

            return $parameters;
        } catch (ReflectionException $e) {
            return [];
        }
    }

    /**
     * Prepare constructor arguments, handling variadic parameters and lazy collections.
     */
    private function prepareConstructorArguments(ReflectionMethod $constructor, array $resolvedValues): array
    {
        $args = [];

        foreach ($constructor->getParameters() as $index => $parameter) {
            $resolvedValue = $resolvedValues[$index];

            if ($parameter->isVariadic()) {
                // Handle variadic parameters (e.g., LoggerInterface ...$loggers)
                if ($resolvedValue instanceof LazyTagCollection) {
                    // Resolve lazy collection to array
                    $args = array_merge($args, $resolvedValue->all());
                } elseif (is_array($resolvedValue)) {
                    $args = array_merge($args, $resolvedValue);
                } else {
                    // If it's not an array, treat as single value
                    $args[] = $resolvedValue;
                }
            } else {
                $args[] = $resolvedValue;
            }
        }

        return $args;
    }

    /**
     * Inject container into object if it has a container property.
     * This allows services to access the container if needed.
     */
    private function injectContainerIfNeeded(object $instance, ReflectionClass $reflector): object
    {
        if (!$reflector->hasProperty('container')) {
            return $instance;
        }

        $property = $reflector->getProperty('container');

        // Only inject if property is not initialized or is null
        if (!$property->isInitialized($instance) || $property->getValue($instance) === null) {
            $property->setValue($instance, $this->container);
        }

        return $instance;
    }
}