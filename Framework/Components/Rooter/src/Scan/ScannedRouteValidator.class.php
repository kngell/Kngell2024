<?php

declare(strict_types=1);
final readonly class ScannedRouteValidator
{
    private const array QUERY_PARAMS_ALLOWED_TYPES = ['array', 'bool', 'int', 'float', 'string'];
    private const array HEADER_PARAMS_ALLOWED_TYPES = ['bool', 'int', 'float', 'string'];

    private function __construct()
    {
    }

    /**
     * @param RouteInfo $route
     * @param ReflectionMethod $method
     * @return void
     * @throws InvalidPathException
     */
    public static function validateRoute(RouteInfo $route, ReflectionMethod $method) : void
    {
        self::validateReturnType($route, $method);
        foreach ($route->getArguments() as $argument) {
            self::validateRequestBody($argument, $method);
            self::validateQueryParam($argument, $method);
            self::validateHeaderParam($argument, $method);
        }
    }

    /**
     * @param RouteInfo $route
     * @param ReflectionMethod $method
     * @return void
     * @throws InvalidPathException
     */
    private static function validateReturnType(RouteInfo $route, ReflectionMethod $method) : void
    {
        if ($route->getReturnType()->isBuiltin()) {
            return;
        }
        $returnClassType = new ReflectionClass($route->getReturnType()->getName());
        if (
            $returnClassType->getName() !== Response::class && ! $returnClassType->isSubclassOf(Response::class) &&
            $route->getResponseBody() === null
        ) {
            throw new InvalidPathException("The Method '{$method->getName()}' from controller '{$method->getDeclaringClass()->getName()}' does not return a response object and does not have a response body attribute");
        }
        if (
            $route->getResponseBody() !== null &&
            $route->getResponseBody()->type === ResponseBodyType::RAW &&
            ! $returnClassType->hasMethod('__toString')
        ) {
            throw new InvalidPathException("The Method '{$method->getName()}' from controller '{$method->getDeclaringClass()->getName()}' has a response Body attribute with the type RAW. But the return object does not have a toString method");
        }
    }

    /**
     * @param RouteArgument $argument
     * @param ReflectionMethod $method
     * @return void
     * @throws InvalidPathException
     */
    private static function validateRequestBody(RouteArgument $argument, ReflectionMethod $method) : void
    {
        if ($argument->getType() !== RouteArgumentType::REQUEST_BODY) {
            return;
        }
        self::validateOptionnalParameter('RequestBody', $argument, $method);
    }

    private static function validateQueryParam(RouteArgument $argument, ReflectionMethod $method) : void
    {
        if ($argument->getType() !== RouteArgumentType::QUERY_PARAM) {
            return;
        }
        self::validateOptionnalParameter('QueryParam', $argument, $method);
        self::validateParameterType('QueryParam', self::QUERY_PARAMS_ALLOWED_TYPES, $argument, $method);
    }

    private static function validateHeaderParam(RouteArgument $argument, ReflectionMethod $method) : void
    {
        if ($argument->getType() !== RouteArgumentType::HEADER_PARAM) {
            return;
        }
        self::validateOptionnalParameter('HeaderParam', $argument, $method);
        self::validateParameterType('HeaderParam', self::HEADER_PARAMS_ALLOWED_TYPES, $argument, $method);
    }

    private static function validateOptionnalParameter(string $type, RouteArgument $argument, ReflectionMethod $method) : void
    {
        if (
            ! $argument->getAttribute()->required &&
            ! $argument->isNullable() &&
            $argument->getDefaultValue() === null
        ) {
            throw new InvalidPathException("The {$type} '\${$argument->getParameterName()}' is optionnal. But there is no default value configured and it's not nullable in method '{$method->getName()}' on class '{$method->getDeclaringClass()->getName()}'");
        }
    }

    /**
     * @param string $type
     * @param string[] $allowedTypes
     * @param RouteArgument $argument
     * @param ReflectionMethod $method
     * @return void
     * @throws InvalidPathException
     */
    private static function validateParameterType(string $type, array $allowedTypes, RouteArgument $argument, ReflectionMethod $method) : void
    {
        if (! in_array($argument->getReflectionType()->getName(), $allowedTypes)) {
            throw new InvalidPathException("The {$type} '\${$argument->getParameterName()}' has the type '{$argument->getReflectionType()->getName()}' in method '{$method->getName()}' on class '{$method->getDeclaringClass()->getName()}'. This type is not support.");
        }
    }
}