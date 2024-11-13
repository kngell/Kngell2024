<?php

declare(strict_types=1);

use JMS\Serializer\Exception\LogicException;
use JMS\Serializer\Exception\NotAcceptableException;
use JMS\Serializer\Exception\RuntimeException;
use JMS\Serializer\Exception\UnsupportedFormatException;
use JMS\Serializer\Serializer;

readonly class RouteArgumentGenerator
{
    private Serializer $serialiser;

    /**
     * @param Serializer $serialiser
     */
    public function __construct(Serializer $serialiser)
    {
        $this->serialiser = $serialiser;
    }

    /**
     * @param RouteInfo $route
     * @param Request $request
     * @return array
     * @throws InvalidPathException
     */
    public function generate(RouteInfo $route, Request $request) : array
    {
        $args = [];
        foreach ($route->getArguments() as $argument) {
            $args[] = match ($argument->getType()) {
                RouteArgumentType::PATH_VARIABLE => $this->getPathVariableArgs($route, $argument, $request),
                RouteArgumentType::HEADER_PARAM => $this->getHeaderParamArgs($route, $argument, $request),
                RouteArgumentType::QUERY_PARAM => $this->getQueryParamArgs($route, $argument, $request),
                RouteArgumentType::REQUEST_BODY => $this->createRequestBodyArgs($route, $argument, $request),
                RouteArgumentType::REQUEST => $request,
            };
        }
        return $args;
    }

    /**
     * @param RouteInfo $route
     * @param RouteArgument $argument
     * @param Request $request
     * @return string
     */
    private function getPathVariableArgs(RouteInfo $route, RouteArgument $argument, Request $request) : string
    {
        /** @var PathVariableAttr $attribute */
        $attribute = $argument->getAttribute();
        $nameToFind = $attribute->name ?? $argument->getParameterName();
        $index = ArrayUtils::findIndex(
            $route->getPath(),
            fn (PathElement $element) => $element->getType() === PathElementType::VARIABLE && $element->getValue() === $nameToFind
        );
        return explode(DIRECTORY_SEPARATOR, $request->getRequestedUri())[$index];
    }

    /**
     * @param RouteInfo $route
     * @param RouteArgument $argument
     * @param Request $request
     * @return string|null
     * @throws InvalidPathException
     */
    private function getHeaderParamArgs(RouteInfo $route, RouteArgument $argument, Request $request) : string|null
    {
        /** @var HeaderParam $attribute */
        $attribute = $argument->getAttribute();
        $name = $attribute->name ?? $argument->getParameterName();
        $value = $request->getHeaders()->get($name);

        return $this->getValueOrDefaultForParam($value, $argument, $route->getMethod());
    }

    /**
     * @param RouteInfo $route
     * @param RouteArgument $argument
     * @param Request $request
     * @return mixed
     * @throws InvalidPathException
     */
    private function getQueryParamArgs(RouteInfo $route, RouteArgument $argument, Request $request) : mixed
    {
        /** @var QueryParam $attribute */
        $attribute = $argument->getAttribute();

        $name = $attribute->name ?? $argument->getParameterName();
        $value = $request->getQuery()->get($name);

        return $this->getValueOrDefaultForParam($value, $argument, $route->getMethod());
    }

    /**
     * @param RouteInfo $route
     * @param RouteArgument $argument
     * @param Request $request
     * @return mixed
     * @throws UnsupportedFormatException
     * @throws RuntimeException
     * @throws LogicException
     * @throws NotAcceptableException
     * @throws InvalidPathException
     */
    private function createRequestBodyArgs(RouteInfo $route, RouteArgument $argument, Request $request) : mixed
    {
        /** @var RequestBody $attribute */
        $attribute = $argument->getAttribute();
        if ($request->hasXmlBody()) {
            return $this->serialiser->deserialize($request->getRawContent(), $argument->getReflectionType()->getName(), 'xml');
        }
        if ($request->hasJsonBody()) {
            return $this->serialiser->deserialize($request->getRawContent(), $argument->getReflectionType()->getName(), 'json');
        }
        if ($request->hasFormUrlEncodedBody() || $request->hasFormDataBody()) {
            $json = json_encode($request->getPost()->getAll());

            return $this->serialiser->deserialize($json, $argument->getReflectionType()->getName(), 'json');
        }
        if (! $attribute->required) {
            return null;
        }

        throw new InvalidPathException("The Request Body '\${$argument->getParameterName()}' is required but we could not found a body or deserialize it in method {$route->getMethod()->getName()} of class {$route->getMethod()->getDeclaringClass()->getName()}.
        Be sure to add a body and a Content-Type Header");
    }

    /**
     * @param mixed $value
     * @param RouteArgument $argument
     * @param ReflectionMethod $method
     * @return string|float|bool|int|array|null
     * @throws InvalidPathException
     */
    private function getValueOrDefaultForParam(mixed $value, RouteArgument $argument, ReflectionMethod $method) : string|float|bool|int|array|null
    {
        /** @var HeaderParam|QueryParam $attribute */
        $attribute = $argument->getAttribute();
        $hasValue = ! StringUtils::isBlanc($value);
        $paramType = match (true) {
            $attribute instanceof HeaderParam => 'HeaderParam',
            $attribute instanceof QueryParam => 'QueryParam',
        };

        if (! $hasValue && $attribute->required) {
            throw new InvalidPathException("The {$paramType} '\${$argument->getParameterName()}' is required in method {$method->getName()} of class {$method->getDeclaringClass()->getName()}.");
        }
        if ($hasValue) {
            return match ($argument->getReflectionType()->getName()) {
                'int' => (int) $value,
                'float' => (float) $value,
                'bool' => filter_var($value, FILTER_VALIDATE_BOOL),
                default => $value
            };
        }
        if ($argument->getDefaultValue() !== null) {
            return $argument->getDefaultValue();
        }
        if ($argument->isNullable()) {
            return null;
        }
        throw new InvalidPathException("The {$paramType} '\${$argument->getParameterName()}' is optionnal. But there is no default value configured or not nullable in method {$method->getName()} of class {$method->getDeclaringClass()->getName()}.");
    }
}