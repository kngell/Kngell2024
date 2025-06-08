<?php

declare(strict_types=1);

readonly class RouteDispatcher
{
    private const GLOBAL_MIDDLEWARES = [
        'previousPage',
        'grantAccess',
        'crsfToken',
    ];

    private const MIDDLEWARE_ORDER = [
        'requireLogin',
        'grantAccess',
    ];

    public function __construct(
        private RouteArgumentGenerator $routeArgumentGenerator,
        private array $middlewares
    ) {
    }

    /**
     * Dispatches the route and returns the response.
     */
    public function dispatch(
        RouteInfo $route,
        string $url,
        App $app,
        array $params,
        Request $request
    ): string|Response {
        try {
            $arguments = ! empty($params) ? $params : $this->routeArgumentGenerator->generate($route, $request);

            // Bind route information for dependency injection
            $app->instance('current.route', $route);
            $app->instance('current.url', $url);
            $app->instance('current.arguments', $arguments);

            // Bind route data with improved parameter binding
            $app->bindParams('Route', [
                'controller' => $route->getController(),
                'method' => $route->getMethod(),
                'arguments' => $arguments,
                'url' => $url,
            ]);

            // Resolve middlewares using improved container
            $middlewareInstances = $this->resolveMiddlewares($route, $app);

            // Use method injection for controller request creation
            $controllerRequest = $app->call(function (App $container) use ($route, $app, $request, $arguments, $url) {
                return $container->resolve(ControllerRequest::class, [
                    'route' => $route,
                    'controller' => $this->resolveController($route, $app, $request),
                    'arguments' => $arguments,
                    'url' => $url,
                ]);
            });

            // Use method injection for middleware request
            return $app->call(function () use ($middlewareInstances, $controllerRequest, $request, $app) {
                return $app->resolve(MiddlewareRequest::class, [
                    'middlewares' => $middlewareInstances,
                    'requestHandler' => $controllerRequest,
                ])->handle($request);
            });
        } catch (DispatchRouteException $th) {
            // Optionally log here
            throw new DispatchRouteException($th->getMessage());
        }
    }

    /**
     * Resolves and instantiates all middlewares for the route.
     */
    private function resolveMiddlewares(RouteInfo $route, App $app): array
    {
        $middlewareNames = $this->getOrderedMiddlewares($route);

        return array_map(function ($name) use ($app, $route) {
            if (! array_key_exists($name, $this->middlewares)) {
                throw new UnexpectedValueException("Middleware $name not found in the configuration route settings");
            }

            $middlewareClass = $this->middlewares[$name];

            // Use contextual binding for middlewares that need route information
            if (in_array($name, ['grantAccess', 'requireLogin'], true)) {
                // Create a factory binding for this specific middleware instance
                $factoryKey = "middleware.{$name}.{$route->getController()}";

                $app->factory($factoryKey, function ($app) use ($middlewareClass, $route) {
                    return $app->resolve($middlewareClass, ['route' => $route]);
                });

                return $app->resolve($factoryKey);
            }

            // For regular middlewares, use standard resolution
            return $app->resolve($middlewareClass);
        }, $middlewareNames);
    }

    /**
     * Returns an ordered list of middleware names for the route.
     */
    private function getOrderedMiddlewares(RouteInfo $route): array
    {
        $routeMiddlewares = $this->extractRouteMiddlewares($route->getRouteParams());
        $allMiddlewares = array_merge(self::GLOBAL_MIDDLEWARES, $routeMiddlewares);

        // Remove duplicates while preserving order
        $allMiddlewares = array_values(array_unique($allMiddlewares));

        // Sort according to MIDDLEWARE_ORDER
        usort($allMiddlewares, function ($a, $b) {
            $order = self::MIDDLEWARE_ORDER;
            $posA = array_search($a, $order, true);
            $posB = array_search($b, $order, true);

            if ($posA !== false && $posB !== false) {
                return $posA - $posB;
            }
            if ($posA !== false) {
                return -1;
            }
            if ($posB !== false) {
                return 1;
            }
            return 0;
        });

        return $allMiddlewares;
    }

    /**
     * Extracts middleware names from route params.
     */
    private function extractRouteMiddlewares(array $params): array
    {
        if (isset($params['middleware'])) {
            return explode('|', $params['middleware']);
        }
        return [];
    }

    /**
     * Resolves and returns the controller instance.
     */
    private function resolveController(RouteInfo $route, App $app, Request $request): Controller
    {
        $this->bindPaymentGateway($app);

        // Bind view environment path
        $path = $this->controllerPath($route->getMethod()->getDeclaringClass()->getFileName());
        $app->bindParams(ViewEnvironment::class, ['path' => $path]);

        // Use method injection to create and configure controller
        return $app->call(function () use ($route, $request, $app) {
            $controller = $app->resolve($route->getController());

            // Use method injection for controller setup instead of manual setter calls
            return $app->call([$this, 'configureController'], [
                'controller' => $controller,
                'request' => $request,
                'app' => $app,
            ]);
        });
    }

    /**
     * Configure controller with all required dependencies.
     */
    private function configureController(Controller $controller, Request $request, App $app): Controller
    {
        return $controller
            ->setRequest($request)
            ->setView($app->resolve(ViewInterface::class))
            ->setresponse($app->getResponse())
            ->setToken($app->resolve(TokenInterface::class))
            ->setFlash($app->resolve(FlashInterface::class))
            ->setSession($app->getSession())
            ->setEventManager($app->resolve(EventManagerInterface::class))
            ->setBuilder($app->resolve(HtmlBuilder::class))
            ->setCache($app->getCache())
            ->setCookie($app->getCookie());
    }

    /**
     * Binds the payment gateway implementation based on the request.
     */
    private function bindPaymentGateway(App $app): void
    {
        $request = $app->getRequest();
        $uri = $request->get('request_uri');

        // Use contextual binding for payment gateways
        if ($uri === '/create-payment' || str_starts_with($uri, '/payments')) {
            $paymentType = $request->getPost()->get('payment_type');

            // Use factory binding for dynamic payment gateway selection
            $app->factory(PaymentGatewayInterface::class, function ($container) use ($paymentType, $uri) {
                if ($paymentType === 'paypal' || str_contains($uri, 'paypal')) {
                    // Tag payment gateway services for easier management
                    $container->bindWithTags(PaypalPaymentGateway::class, null, ['payment_gateway', 'paypal']);
                    $container->bindWithTags(PaypalApiClient::class, null, ['api_client', 'paypal']);

                    $container->bind(ApiClientInterface::class, PaypalApiClient::class);
                    return $container->resolve(PaypalPaymentGateway::class);
                }

                // Default payment gateway
                return $container->resolve(DefaultPaymentGateway::class);
            });
        }
    }

    /**
     * Returns the controller path for view binding.
     */
    private function controllerPath(string $path): string
    {
        if (str_contains($path, 'Client')) {
            return 'Frontend';
        }
        if (str_contains($path, 'Admin')) {
            return 'Backend';
        }
        return '';
    }
}