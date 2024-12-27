<?php

declare(strict_types=1);

use JMS\Serializer\Serializer;
use JMS\Serializer\SerializerBuilder;

readonly class RouteArgumentGenerator
{
    private Serializer $serialiser;

    /**
     * @param Serializer $serialiser
     */
    public function __construct()
    {
        $this->serialiser = SerializerBuilder::create()->build();
    }

    /**
     * @param RouteInfo $route
     * @param Request $request
     * @return array
     */
    public function generate(RouteInfo $route, Request $request) : array
    {
        $args = [];
        $urlRouteParams = $route->getRouteParams();
        foreach ($route->getArguments() as $argument) {
            if (array_key_exists($argument->getParameterName(), $urlRouteParams)) {
                $args[$argument->getParameterName()] = $urlRouteParams[$argument->getParameterName()];
            } elseif (array_key_exists('arguments', $urlRouteParams)) {
                $args[$argument->getParameterName()] = $urlRouteParams['arguments'];
            } elseif ($argument->isDefaultValue()) {
                $args[$argument->getParameterName()] = $argument->getDefaultValue();
            }
            $body = $this->createRequestBodyArgs($argument, $request);
        }
        $this->validatePathVariable($route, $urlRouteParams);
        return is_iterable($args) ? $args : [];
    }

    private function validatePathVariable(RouteInfo $route, array $urlRouteParams) : void
    {
        /** @var PathElement $path */
        foreach ($route->getPath() as $path) {
            if ($path->getType()->name === 'VARIABLE') {
                if (array_key_exists($path->getValue(), $urlRouteParams)) {
                    unset($urlRouteParams[$path->getValue()]);
                }
            }
        }
        if (array_key_exists('id', $urlRouteParams)) {
            $arrImpl = array_key_exists('id', $urlRouteParams) ? ['id' => $urlRouteParams['id']] : [];
            throw new InvalidRouteArgumentException(sprintf('The arguments %s from the url does not match the method %s arguments', implode(',', $arrImpl), $route->getMethod()->getName()));
        }
    }

    private function createRequestBodyArgs(RouteArguments $argument, Request $request) : mixed
    {
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
        return null;
    }
}