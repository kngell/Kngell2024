<?php

declare(strict_types=1);
class Rooter implements RooterInterface
{
    private RouteMatcher $routeMatcher;
    private RouteArgumentGenerator $routeArgumentGenerator;
    private RouteResponseGenerator $routeResponseGenerator;

    /**
     * @param RouteMatcher $routeMatcher
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

    public function handle(Request $request, ?App $app = null, string|null $url = null, array $params = []) : Response
    {
        $route = $this->routeMatcher->match($request, $url);
        if ($route === null) {
            throw new PageNotFoundException('Page not Found');
        }
        $results = $this->processRoute($route, $url, $app, $params);
        if (! empty($params) && array_key_exists('code', $params)) {
            $responseStatus = $this->getResponseStatus($params);
        }
        if ($results instanceof Response) {
            return $results;
        }
        return $this->routeResponseGenerator->generate(
            isset($responseStatus) ? $responseStatus : $route->getResponseStatus(),
            $results
        );
    }

    private function processRoute(RouteInfo $route, string $url, App $app, array $params) : string|Response
    {
        $path = $this->controlllerPath(
            $route->getMethod()->getDeclaringClass()->getFileName()
        );
        $app->bind(ViewEnvironment::class, null, false, $path);
        $arguments = ! empty($params) ? $params : $this->routeArgumentGenerator->generate($route);
        if (in_array($url, ['/_404_error', '/_500_error'])) {
            return $route->getMethod()->invoke(
                $app->get($route->getController()),
                $arguments
            );
        } else {
            return $route->getMethod()->invoke(
                $app->get($route->getController()),
                ...$arguments
            );
        }
    }

    private function getResponseStatus(array $params) : ResponseStatus
    {
        $errorCode = $params['code'];
        $code = HttpStatusCode::from($errorCode);
        return new ResponseStatus($code);
    }

    private function controlllerPath(string $path) : string
    {
        if (str_contains($path, 'Client')) {
            return 'Frontend';
        }
        if (str_contains($path, 'Admin')) {
            return 'Admin';
        } else {
            return '';
        }
    }
}