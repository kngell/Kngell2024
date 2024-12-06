<?php

declare(strict_types=1);

final readonly class DependencyResolverFactory
{
    private function __construct()
    {
    }

    /**
     * @param ReflectionNamedType|ReflectionUnionType|ReflectionIntersectionType|null $type
     * @param ReflectionParameter $parameter
     * @param mixed $args
     * @return DependenciesResolverInterface
     * @throws DependencyHasNoValueException
     */
    public static function create(ReflectionNamedType|ReflectionUnionType|ReflectionIntersectionType|null &$type, ReflectionParameter $parameter, mixed $args = []) :  DependenciesResolverInterface
    {
        if ($type instanceof ReflectionUnionType) {
            $types = $type->getTypes();
            foreach ($types as $rType) {
                if ($rType->isBuiltin() && ! empty($args)) {
                    $type = $rType;
                    return self::resolver($rType, $parameter, $args);
                }
            }
            return self::resolver($rType, $parameter, $args);
        } else {
            return self::resolver($type, $parameter, $args);
        }
    }

    private static function resolver(ReflectionNamedType $type, ReflectionParameter $parameter, mixed $args = []) : DependenciesResolverInterface
    {
        return match (true) {
            $type === null => new ContainerTypeErrorResolver($type, $parameter),
            $type->isBuiltin() => new BuiltInDependencyResolver($type, $parameter, $args),
            ! $type->isBuiltin() => new ParameterDependecyResolver($type, $parameter, $args),
            default => throw new DependencyHasNoValueException('Could not resolve class dependency ' . $parameter->name)
        };
    }
}