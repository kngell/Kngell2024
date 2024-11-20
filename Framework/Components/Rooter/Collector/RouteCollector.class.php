<?php

declare(strict_types=1);

class RouteCollector
{
    private array $routes;

    public function __construct(array $routingTable = [])
    {
        $routingFile = ROOT_DIR . '/App/Config/routes.yaml';
        $this->routes = YamlFile::get($routingFile);
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