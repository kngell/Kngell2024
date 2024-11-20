<?php

declare(strict_types=1);

readonly class RouteArgumentGenerator
{
    public function __construct()
    {
    }

    /**
     * @param RouteInfo $route
     * @param Request $request
     * @return array
     */
    public function generate(RouteInfo $route) : array
    {
        $args = [];
        $urlArguments = $this->urlArguments($route);
        foreach ($route->getArguments() as $argument) {
            $args[$argument->getName()] = $argument->getValue();
        }
        if ($args !== $urlArguments) {
            throw new InvalidRouteArgumentException(sprintf('The arguments %s from the url does not match the method %s arguments', implode(',', $urlArguments), $route->getMethod()->getName()));
        }
        return is_iterable($args) ? $args : [];
    }

    private function urlArguments(RouteInfo $route) : array
    {
        $params = [];
        foreach ($route->getRouteParams() as $name => $param) {
            if (! in_array($name, ['controller', 'method'])) {
                $params[$name] = $param;
            }
        }
        return $params;
    }
}