<?php

declare(strict_types=1);
class Rooter implements RooterInterface
{
    private RouteMatcher $routeMatcher;
    private RouteDispatcher $routeDispatcher;
    private RouteResponseGenerator $routeResponseGenerator;

    public function __construct(
        RouteMatcher $routeMatcher,
        RouteDispatcher $routeDispatcher,
        RouteResponseGenerator $routeResponseGenerator
    ) {
        $this->routeMatcher = $routeMatcher;
        $this->routeDispatcher = $routeDispatcher;
        $this->routeResponseGenerator = $routeResponseGenerator;
    }

    public function handle(Request $request, ?App $app = null, string|null $url = null, array $params = []) : Response
    {
        $route = $this->routeMatcher->match($request, $url);
        if ($route === null) {
            throw new PageNotFoundException("Page not Found with method {$request->getServer()->get('request_method')}");
        }

        $results = $this->routeDispatcher->dispatch($route, $url, $app, $params, $request);

        if (! empty($params) && array_key_exists('code', $params)) {
            $responseStatus = $this->getResponseStatus($params);
        }

        if ($results instanceof Response) {
            return $results;
        }
        return $this->routeResponseGenerator->generate(
            $route->getResponseBody(),
            isset($responseStatus) ? $responseStatus : $route->getResponseStatus(),
            $results,
            $app->getResponse()
        );
    }

    private function getResponseStatus(array $params) : ResponseStatus
    {
        $errorCode = $params['code'];
        return new ResponseStatus(HttpStatusCode::from($errorCode));
    }
}