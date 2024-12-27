<?php

declare(strict_types=1);

final class RouteCollector
{
    private array $routes;

    public function __construct()
    {
        $this->routes = App::getInstance()->getRoutes();
    }

    /**
     * Get the value of routes.
     *
     * @return array
     */
    public function getRoutes(): array
    {
        return $this->routes;
    }

    public function routeMiddleware() : void
    {
        foreach ($this->routes as $route => $params) {
            if (array_key_exists('httpmethod', $params) && $params['httpmethod'] === 'post') {
                if (array_key_exists('middleware', $params)) {
                    $params['middleware'] = 'csrfToken|' . $params['middleware'];
                } else {
                    $params['middleware'] = 'csrfToken';
                }
            }
        }
    }
}