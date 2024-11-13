<?php

declare(strict_types=1);

final readonly class ReflectionTypeValidator
{
    private function __construct()
    {
    }

    public static function validateConstructorParameterType(ReflectionParameter $parameter, string $serviceClass) : void
    {
        $name = $parameter->getName();
        $type = $parameter->getType();

        if ($type === null) {
            throw new ReflectionTypeException("The constructor parameter '{$name}' in class '{$serviceClass}' does not have a type. Type is required.");
        }

        if ($type instanceof ReflectionUnionType || $type instanceof ReflectionIntersectionType) {
            throw new ReflectionTypeException("The constructor parameter '{$name}' in class '{$serviceClass}' has multiples types. This is not yet support");
        }
    }

    public static function validateMethodReturnType(ReflectionMethod $method) : void
    {
        $serviceClass = $method->getDeclaringClass()->getName();
        $methodName = $method->getName();
        $type = $method->getReturnType();

        if ($type === null) {
            throw new ReflectionTypeException("The return type of method '{$methodName}' in class '{$serviceClass}' does not have a type. A return Type is required");
        }

        if ($type instanceof ReflectionUnionType || $type instanceof ReflectionIntersectionType) {
            throw new ReflectionTypeException("The return type of method '{$methodName}' in class '{$serviceClass}' has multiples types. This is not yet support");
        }
    }

    public static function validateMethodParameterType(ReflectionParameter $parameter, string $methodName, string $className) : void
    {
        $name = $parameter->getName();
        $type = $parameter->getType();

        if ($type === null) {
            throw new ReflectionTypeException("The parameter '{$name}' in class '{$className}' does not have a type. Type is required.");
        }

        if ($type instanceof ReflectionUnionType || $type instanceof ReflectionIntersectionType) {
            throw new ReflectionTypeException("The parameter '{$name}' in class '{$className}' of method '{$methodName}' has multiples types. This is not yet support");
        }
    }
}