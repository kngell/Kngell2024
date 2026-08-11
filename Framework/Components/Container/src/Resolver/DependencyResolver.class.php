<?php

declare(strict_types=1);

/**
 * Handles resolution of constructor and method parameters.
 * Extracted from Container to reduce complexity and improve testability.
 */
class DependencyResolver
{
    private Container $container;
    private array $globalParameters = [];
    private array $tags = [];
    private bool $autoWiring = true;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getContainer(): Container
    {
        return $this->container;
    }

    public function setGlobalParameters(array $parameters): void
    {
        $this->globalParameters = $parameters;
    }

    public function setTags(array $tags): void
    {
        $this->tags = $tags;
    }

    public function setAutoWiring(bool $enabled): void
    {
        $this->autoWiring = $enabled;
    }

    /**
     * Resolve constructor dependencies for a class.
     */
    public function resolveConstructorDependencies(
        ReflectionMethod $constructor,
        array $parameters = [],
        ?BindingDefinition $binding = null,
    ): array {
        $dependencies = [];
        $bindingParameters = $binding?->parameters ?? [];
        $allParameters = array_merge($this->globalParameters, $bindingParameters, $parameters);

        foreach ($constructor->getParameters() as $parameter) {
            $dependencies[] = $this->resolveDependency($parameter, $allParameters);
        }

        return $dependencies;
    }

    /**
     * Resolve method dependencies for a callable.
     */
    public function resolveMethodDependencies(
        ReflectionFunctionAbstract $reflector,
        array $parameters = [],
    ): array {
        $dependencies = [];

        foreach ($reflector->getParameters() as $parameter) {
            $dependencies[] = $this->resolveDependency($parameter, $parameters);
        }

        return $dependencies;
    }

    /**
     * Resolve a single dependency parameter.
     */
    private function resolveDependency(ReflectionParameter $parameter, array $parameters = []): mixed
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
            if ($parameter->isVariadic()) {
                return $this->resolveVariadicDependency($type->getName());
            }
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
            "Cannot resolve parameter [{$name}]",
        );
    }

    private function resolveVariadicDependency(string $typeName): LazyTagCollection
    {
        if (!empty($this->tags[$typeName] ?? [])) {
            return $this->container->taggedLazy($typeName);
        }

        $tagName = $this->inferTagFromType($typeName);
        if (!empty($this->tags[$tagName] ?? [])) {
            return $this->container->taggedLazy($tagName);
        }

        return new LazyTagCollection($this->container, $typeName, []);
    }

    private function resolveTypedDependency(ReflectionParameter $parameter, ReflectionNamedType $type): mixed
    {
        $typeName = $type->getName();

        // Handle built-in types
        if ($type->isBuiltin()) {
            return $this->resolveBuiltinType($parameter, $typeName);
        }

        // Handle class/interface types
        try {
            return $this->container->resolve($typeName);
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

    private function resolveBuiltinType(ReflectionParameter $parameter, string $typeName): mixed
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
                "Cannot resolve built-in type [{$typeName}] for parameter [{$name}]",
            )
        };
    }

    private function resolveUnionTypeDependency(ReflectionParameter $parameter, ReflectionUnionType $type): mixed
    {
        $types = $type->getTypes();

        foreach ($types as $unionType) {
            if ($unionType instanceof ReflectionNamedType) {
                try {
                    if ($unionType->isBuiltin()) {
                        return $this->resolveBuiltinType($parameter, $unionType->getName());
                    } else {
                        return $this->container->resolve($unionType->getName());
                    }
                } catch (Throwable) {
                    continue;
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
            "Cannot resolve union type for parameter [{$parameter->getName()}]",
        );
    }

    private function resolveIntersectionTypeDependency(ReflectionParameter $parameter, ReflectionIntersectionType $type): mixed
    {
        $types = $type->getTypes();

        if (!empty($types) && $types[0] instanceof ReflectionNamedType) {
            try {
                return $this->container->resolve($types[0]->getName());
            } catch (Throwable) {
                // Fall through
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
            "Cannot resolve intersection type for parameter [{$parameter->getName()}]",
        );
    }

    private function inferTagFromType(string $typeName): string
    {
        $short = (CustomReflection::getInstance($typeName)->getClass())->getShortName();
        $snake = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $short));

        return str_ends_with($snake, 'y')
            ? substr($snake, 0, -1) . 'ies'
            : $snake . 's';
    }

    private function castToType(mixed $value, string $type): mixed
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
}