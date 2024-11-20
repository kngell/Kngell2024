<?php

declare(strict_types=1);

final readonly class DependencyResolverFactory
{
    private function __construct()
    {
    }

    public static function create(ReflectionNamedType|ReflectionUnionType|ReflectionIntersectionType|null $type, ReflectionParameter $parameter, mixed $args = []) : DependenciesResolverInterface
    {
        return match (true) {
            $type === null => new ContainerTypeErrorResolver($type, $parameter),
            $type->isBuiltin() => new BuiltInDependencyResolver($parameter, $args),
            ! $type->isBuiltin() => new ParameterDependecyResolver($parameter, $args),
            default => throw new DependencyHasNoValueException('Could not resolve class dependency ' . $parameter->name)
        };
    }
}