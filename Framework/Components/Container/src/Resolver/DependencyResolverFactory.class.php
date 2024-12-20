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
    public static function create(ReflectionNamedType|ReflectionUnionType|ReflectionIntersectionType|null &$type, ReflectionParameter $parameter) :  DependenciesResolverInterface
    {
        if ($type instanceof ReflectionUnionType) {
            $types = $type->getTypes();
            foreach ($types as $rType) {
                if ($rType->isBuiltin()) {
                    $type = $rType;
                    return self::resolver($rType, $parameter);
                }
            }
            return self::resolver($rType, $parameter);
        } else {
            return self::resolver($type, $parameter);
        }
    }

    private static function resolver(ReflectionNamedType $type, ReflectionParameter $parameter) : DependenciesResolverInterface
    {
        $name = $type->getName();
        return match (true) {
            $type === null => new ContainerTypeErrorResolver($type, $parameter),
            $type->isBuiltin() && $type->getName() !== 'object' => new BuiltInDependencyResolver($type, $parameter),
            ! $type->isBuiltin() || $type->getName() === 'object' => new ParameterDependecyResolver($type, $parameter),

            default => throw new DependencyHasNoValueException('Could not resolve class dependency ' . $parameter->name)
        };
    }
}