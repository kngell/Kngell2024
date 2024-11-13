<?php

declare(strict_types=1);
class RouteScanner
{
    /**
     * @param ServiceContainerInterface $container
     * @return RouteInfo[]
     */
    public function scanForRoutes(ServiceContainerInterface $container) : array
    {
        $controllers = $container->getAllBeansByAttributeType(ControllerAttr::class);
        $routes = [];
        foreach ($controllers as $controller) {
            $routes = array_merge($routes, $this->scanForRoutesInTheController($controller));
        }
        return $routes;
    }

    /**
     * @param BeanInfo $controller
     * @return BeanInfo[]
     * @throws InvalidPathException
     * @throws ReflectionTypeException
     * @throws ReflectionException
     */
    private function scanForRoutesInTheController(BeanInfo $controller) : array
    {
        $routes = [];
        $class = new ReflectionObject($controller->getService());
        foreach ($class->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            $pathAttribute = $method->getAttributes(PathAttr::class, ReflectionAttribute::IS_INSTANCEOF);

            if (empty($pathAttribute)) {
                continue;
            }
            if (count($pathAttribute) > 1) {
                throw new InvalidPathException("The method '{$method->getName()}' from controller '{$class->getName()}' has more than one path attributes");
            }
            ReflectionTypeValidator::validateMethodReturnType($method);

            /**
             * @var PathAttr
             */
            $pathAttribute = ArrayUtils::first($pathAttribute)->newInstance();
            $builder = new RouteInfoBuilder();
            $route = $builder
                ->withController($controller)
                ->withHttpMethod($pathAttribute->method)
                ->withPath($this->stripPath($pathAttribute->path))
                ->withArguments($this->getRouteArguments($method->getParameters(), $method->getName(), $class->getName()))
                ->withResponseBody($this->getFirstAttributeOrNull($method->getAttributes(ResponseBody::class, ReflectionAttribute::IS_INSTANCEOF)))
                ->withResponseStatus($this->getFirstAttributeOrNull($method->getAttributes(ResponseStatus::class, ReflectionAttribute::IS_INSTANCEOF)))
                ->withReturnType($method->getReturnType())
                ->withMethod($method)
                ->build();

            //Validation
            ScannedRouteValidator::validateRoute($route, $method);
            $routes[] = $route;
        }
        return $routes;
    }

    /**
     * @param string $path
     * @return PathElement[]
     */
    private function stripPath(string $path) : array
    {
        $pathElements = [];
        foreach (explode(DIRECTORY_SEPARATOR, $path) as $part) {
            $builder = new PathElementBuilder();
            if (str_starts_with($part, '{') && str_ends_with($part, '}')) {
                $builder->withType(PathElementType::VARIABLE)
                    ->withValue(ltrim(rtrim($part, '}'), '{'));
            } else {
                $builder->withType(PathElementType::NORMAL)
                    ->withValue($part);
            }

            $pathElements[] = $builder->build();
        }
        return $pathElements;
    }

    /**
     * @param ReflectionParameter[] $parameters
     * @param string $methodName
     * @param string $classame
     * @return RouteArgument[]
     */
    private function getRouteArguments(array $parameters, string $methodName, string $className) : array
    {
        if (empty($parameters)) {
            return [];
        }

        $routeArguments = [];
        foreach ($parameters as $parameter) {
            ReflectionTypeValidator::validateMethodParameterType($parameter, $className, $methodName);

            /** @var ReflectionNamedType $reflectionType */
            $reflectionType = $parameter->getType();

            $builder = new RouteArgumentBuilder();
            $builder->withParameterName($parameter->getName())
                ->withReflectionType($reflectionType)
                ->withNullable($parameter->allowsNull() || $reflectionType->allowsNull());
            if ($parameter->isDefaultValueAvailable()) {
                $builder->withDefaultValue($parameter->getDefaultValue());
            }
            if ($reflectionType->getName() === Request::class || is_subclass_of($reflectionType->getName(), Request::class)) {
                $builder->withType(RouteArgumentType::REQUEST)
                    ->withAttribute(null);
            } else {
                $attributes = $parameter->getAttributes();
                if (count($attributes) !== 1) {
                    throw new InvalidPathException("The Parameter '{$parameter->getName()}' from method '{$methodName}' from controller '{$className}' should have exactle one attribute or be part of the request type");
                }
                /** @var ReflectionAttribute $attribute */
                $attribute = ArrayUtils::first($attributes);

                $type = match ($attribute->getName()) {
                    PathVariableAttr::class => RouteArgumentType::PATH_VARIABLE,
                    QueryParam::class => RouteArgumentType::QUERY_PARAM,
                    HeaderParam::class => RouteArgumentType::HEADER_PARAM,
                    RequestBody::class => RouteArgumentType::REQUEST_BODY,
                    RequestBody::class => RouteArgumentType::REQUEST_BODY,
                    default => throw new InvalidPathException("The Parameter '{$parameter->getName()}' from method '{$methodName}' from controller '{$className}' has an attribute that we do not support")
                };
                $attributeInstance = $attribute->newInstance();
                if (
                    ($type === RouteArgumentType::QUERY_PARAM ||
                    $type === RouteArgumentType::HEADER_PARAM) && $attributeInstance->defaultValue !== null
                ) {
                    $builder->withDefaultValue($attributeInstance->defaultValue);
                }

                $builder->withType($type)->withAttribute($attributeInstance);
            }
            $routeArguments[] = $builder->build();
        }
        return $routeArguments;
    }

    private function getFirstAttributeOrNull(array $attributes) : mixed
    {
        if (count($attributes) === 0) {
            return null;
        }
        return ArrayUtils::first($attributes)->newInstance();
    }
}