<?php

declare(strict_types=1);
readonly class RouteDispatcher
{
    public function __construct(
        private RouteArgumentGenerator $routeArgumentGenerator,
        private array $middlewares
    ) {
    }

    public function dispatch(RouteInfo $route, string $url, App $app, array $params, Request $request) : string|Response
    {
        $arguments = ! empty($params) ? $params : $this->routeArgumentGenerator->generate($route, $request);
        $controllerObject = $this->controller($route, $app);
        return $app->get(MiddlewareRequest::class, [
            $this->middlewares($route, $app),
            $app->get(ControllerRequest::class, [
                $route,
                $controllerObject,
                $arguments,
                $url,
            ]),
        ])->handle($request);
    }

    /**
     * @param RouteInfo $route
     * @return RequestHandlerInterface[]
     */
    private function middlewares(RouteInfo $route, App $app) : array
    {
        if (! array_key_exists('middleware', $route->getRouteParams())) {
            return [];
        }
        $middlewares = explode('|', $route->getRouteParams()['middleware']);
        array_walk($middlewares, function (&$value) use ($app) {
            if (! array_key_exists($value, $this->middlewares)) {
                throw new UnexpectedValueException("Middleware $value not found in the configuration route settings");
            }
            $value = $app->get($this->middlewares[$value]);
        });
        return $middlewares;
    }

    private function controller(RouteInfo $route, App $app) : Controller
    {
        $path = $this->controlllerPath(
            $route->getMethod()->getDeclaringClass()->getFileName()
        );
        $app->bind(ViewEnvironment::class, null, false, $path);
        return $app->get($route->getController())
            ->setRequest($app->getRequest())
            ->setView($app->get(ViewInterface::class))
            ->setFormBuilder($app->get(FormBuilder::class))
            ->setresponse($app->getResponse())
            ->setSession($app->getSession())
            ->setToken($app->get(TokenInterface::class));
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