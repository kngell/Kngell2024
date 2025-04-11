<?php

declare(strict_types=1);

use JMS\Serializer\Exception\NotAcceptableException;
use JMS\Serializer\Exception\RuntimeException;
use JMS\Serializer\Exception\UnsupportedFormatException;

readonly class RouteDispatcher
{
    private const array GLOBAL_MIDDLEWARES = ['return_to', 'grantAccess']; //'return_to',

    public function __construct(
        private RouteArgumentGenerator $routeArgumentGenerator,
        private array $middlewares
    ) {
    }

    /**
     * @param RouteInfo $route
     * @param string $url
     * @param App $app
     * @param array $params
     * @param Request $request
     * @return string|Response
     * @throws DispatchRouteException
     * @throws UnsupportedFormatException
     * @throws RuntimeException
     * @throws NotAcceptableException
     * @throws InvalidRouteArgumentException
     * @throws BindingResolutionException
     * @throws ReflectionException
     * @throws DependencyHasNoDefaultValueException
     */
    public function dispatch(RouteInfo $route, string $url, App $app, array $params, Request $request) : string|Response
    {
        try {
            $arguments = ! empty($params) ? $params : $this->routeArgumentGenerator->generate($route, $request);
            $controllerObject = $this->controller($route, $app);
            $app->bind('Route', null, false, [
                $route->getController(),
                $route->getMethod(),
            ]);
            return $app->get(MiddlewareRequest::class, [
                $this->middlewares($route, $app),
                $app->get(ControllerRequest::class, [
                    $route,
                    $controllerObject,
                    $arguments,
                    $url,
                ]),
            ])->handle($request);
        } catch (DispatchRouteException $th) {
            throw new DispatchRouteException($th->getMessage());
        }
    }

    /**
     * @param RouteInfo $route
     * @return RequestHandlerInterface[]
     */
    private function middlewares(RouteInfo $route, App $app) : array
    {
        $middlewares = $this->addMiddleware($route->getRouteParams());
        $middlewares = explode('|', $middlewares);
        array_walk($middlewares, function (&$value) use ($app, $route) {
            if (! array_key_exists($value, $this->middlewares)) {
                throw new UnexpectedValueException("Middleware $value not found in the configuration route settings");
            }
            if (in_array($value, ['grantAccess'])) {
                $value = $app->get($this->middlewares[$value], [$route]);
            } else {
                $value = $app->get($this->middlewares[$value]);
            }
        });
        return $middlewares;
    }

    private function addMiddleware(array $matches) : string
    {
        if (isset($matches['middleware'])) {
            $middlewares = explode('|', $matches['middleware']);
        }
        $middlewares = array_merge($middlewares ?? [], self::GLOBAL_MIDDLEWARES);
        if (! empty($middlewares)) {
            return implode('|', $middlewares);
        }
        return '';
    }

    /**
     * @param RouteInfo $route
     * @param App $app
     * @return Controller
     * @throws BindingResolutionException
     * @throws ReflectionException
     * @throws DependencyHasNoDefaultValueException
     */
    private function controller(RouteInfo $route, App $app) : Controller
    {
        $path = $this->controlllerPath(
            $route->getMethod()->getDeclaringClass()->getFileName()
        );
        $app->bind(ViewEnvironment::class, null, false, $path);
        return $app->get($route->getController())
            ->setRequest($app->getRequest())
            ->setView($app->get(ViewInterface::class))
            ->setresponse($app->getResponse())
            ->setToken($app->get(TokenInterface::class))
            ->setFlash($app->get(FlashInterface::class))
            ->setSession($app->getSession())
            ->setEventManager($app->get(EventManagerInterface::class));
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