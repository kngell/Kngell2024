<?php

declare(strict_types=1);

class RouteMatcher
{
    /** @var RouteInfo[] */
    private array $routes;

    /**
     * @param RouteInfo[] $routes
     * @return void
     */
    public function __construct(array $routes)
    {
        $this->routes = $routes;
    }

    public function match(Request $request) : RouteInfo|null
    {
        $uriParts = explode(DIRECTORY_SEPARATOR, $request->getRequestedUri());
        $match = false;
        foreach ($this->routes as $route) {
            if ($route->getHttpMethod() !== $request->getMethod()) {
                continue;
            }
            if (count($uriParts) !== count($route->getPath())) {
                continue;
            }
            $match = true;
            for ($i = 0; $i < count($uriParts); $i++) {
                $routePart = $route->getPath()[$i];
                $uriPart = $uriParts[$i];
                if ($routePart->getType() !== PathElementType::VARIABLE && $routePart->getValue() !== $uriPart) {
                    $match = false;
                    break;
                }
            }
            if ($match) {
                return $route;
            }
        }
        return null;
    }
}
