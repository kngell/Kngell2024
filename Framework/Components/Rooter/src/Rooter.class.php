<?php

declare(strict_types=1);
readonly class Rooter
{
    private RouteMatcher $routeMatcher;
    private RouteArgumentGenerator $routeArgumentGenerator;
    private RouteResponseGenerator $routeResponseGenerator;

    /**
     * @param RouteMatcher $routeMatcher
     * @param RouteArgumentGenerator $routeArgumentGenerator
     * @param RouteResponseGenerator $routeResponseGenerator
     */
    public function __construct(
        RouteMatcher $routeMatcher,
        RouteArgumentGenerator $routeArgumentGenerator,
        RouteResponseGenerator $routeResponseGenerator
    ) {
        $this->routeMatcher = $routeMatcher;
        $this->routeArgumentGenerator = $routeArgumentGenerator;
        $this->routeResponseGenerator = $routeResponseGenerator;
    }

    public function handle(Request $request) : Response
    {
        $route = $this->routeMatcher->match($request);
        if ($route === null) {
            throw new RouteNotFoundException('Route not Found');
        }
        $controller = $route->getController()->getService();
        $arguments = $this->routeArgumentGenerator->generate($route, $request);

        $results = $route->getMethod()->invoke($controller, ...$arguments);

        if ($results instanceof Response) {
            return $results;
        }
        return $this->routeResponseGenerator->generate($route->getResponseBody(), $route->getResponseStatus(), $results);
    }
}