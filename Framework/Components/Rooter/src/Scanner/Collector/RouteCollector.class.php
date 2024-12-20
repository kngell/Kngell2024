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
}