<?php

declare(strict_types=1);

final readonly class RouteDispatcher
{
    private const GLOBAL_MIDDLEWARES = [
        'previousPage',
        'auth',
        'grantAccess',
        'crsfToken',
        'cacheGarbageCollector',
        'mergeFlash',
    ];

    private const MIDDLEWARE_ORDER = [
        'auth',           // Authentication (must be first)
        'requireLogin',   // Login enforcement
        'grantAccess',    // Authorization
    ];

    public function __construct(
        private RouteArgumentGenerator $routeArgumentGenerator,
        private array $globalMiddlewares,
    ) {
    }

    public function dispatch(
        RouteInfo $route,
        string $url,
        App $app,
        array $params,
        Request $request,
    ): string|Response {
        try {
            $arguments = !empty($params) ? $params : $this->routeArgumentGenerator->generate($route, $request);
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
            throw new DispatchRouteException($th->getMessage());
        }
    }

    private function resolveMiddlewares(RouteInfo $route, App $app): array
    {
        $middlewareNames = $this->getOrderedMiddlewares($route);
        $this->ensureAuthMiddlewareFirst($middlewareNames);

        return array_map(function ($name) use ($app, $route) {
            if (!array_key_exists($name, $this->globalMiddlewares)) {
                throw new UnexpectedValueException("Middleware $name not found in the configuration route settings");
            }

            $middlewareClass = $this->globalMiddlewares[$name];

            if (in_array($name, ['auth', 'grantAccess', 'requireLogin', 'csrfToken'], true)) {
                $factoryKey = "middleware.{$name}.{$route->getController()}";

                $app->factory($factoryKey, function ($app) use ($middlewareClass, $route) {
                    return $app->make($middlewareClass, ['route' => $route]);
                });

                return $app->make($factoryKey);
            }
            return $app->make($middlewareClass);
        }, $middlewareNames);
    }

    private function ensureAuthMiddlewareFirst(array &$middlewareNames): void
    {
        $hasAuthRelatedMiddleware = in_array('requireLogin', $middlewareNames) || in_array('grantAccess', $middlewareNames);

        $hasAuthMiddleware = in_array('auth', $middlewareNames);

        if ($hasAuthRelatedMiddleware && !$hasAuthMiddleware) {
            array_unshift($middlewareNames, 'auth');
        } elseif ($hasAuthMiddleware && $hasAuthRelatedMiddleware) {
            $authIndex = array_search('auth', $middlewareNames);
            if ($authIndex > 0) {
                unset($middlewareNames[$authIndex]);
                $middlewareNames = array_values($middlewareNames);
                array_unshift($middlewareNames, 'auth');
            }
        }
    }

    private function getOrderedMiddlewares(RouteInfo $route): array
    {
        $routeMiddlewares = $this->extractRouteMiddlewares($route->getRouteParams());
        $allMiddlewares = array_merge(self::GLOBAL_MIDDLEWARES, $routeMiddlewares);

        $allMiddlewares = array_values(array_unique($allMiddlewares));

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

    private function extractRouteMiddlewares(array $params): array
    {
        if (isset($params['middleware'])) {
            if (is_array($params['middleware'])) {
                return $params['middleware'];
            }
            if (is_string($params['middleware'])) {
                return explode('|', $params['middleware']);
            } else {
                return [];
            }
        }
        return [];
    }

    private function resolveController(RouteInfo $route, App $app, Request $request): Controller
    {
        $this->bindPaymentGateway($app);

        $path = $this->controllerPath($route->getMethod()->getDeclaringClass()->getFileName());
        $app->bindParams(ViewEnvironment::class, ['path' => $path]);

        $controller = $app->resolve($route->getController());

        return $this->configureController($controller, $request, $app);
    }

    private function configureController(Controller $controller, Request $request, App $app): Controller
    {
        $start = microtime(true);

        $controller
            ->setApp($app)
            ->setRequest($request)
            ->setResponse($app->getResponse())
            ->initializeHtmlCache($app->make(HtmlPageCacheFactory::class));

        $time = (microtime(true) - $start) * 1000;
        error_log(sprintf(
            '[DISPATCHER] Controller %s configured in %.2f ms (lazy loading enabled)',
            get_class($controller),
            $time,
        ));

        return $controller;
    }

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