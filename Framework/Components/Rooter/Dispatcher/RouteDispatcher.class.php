<?php

declare(strict_types=1);
readonly class RouteDispatcher
{
    private RouteArgumentGenerator $routeArgumentGenerator;

    public function __construct(
        RouteArgumentGenerator $routeArgumentGenerator,
    ) {
        $this->routeArgumentGenerator = $routeArgumentGenerator;
    }

    public function dispatch(RouteInfo $route, string $url, App $app, array $params) : string|Response
    {
        $arguments = ! empty($params) ? $params : $this->routeArgumentGenerator->generate($route);
        $controllerObject = $this->controller($route, $app);
        if (in_array($url, ['/_404_error', '/_500_error'])) {
            return $route->getMethod()->invoke($controllerObject, $arguments);
        } else {
            return $route->getMethod()->invoke($controllerObject, ...$arguments);
        }
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
            ->setFormBuilder($app->get(FormBuilder::class));
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