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

    private function resolveMiddlewares(RouteInfo $route, App $app): array
    {
        // Get middleware names in the correct execution order
        $middlewareNames = $this->getOrderedMiddlewares($route);
        $this->ensureAuthMiddlewareFirst($middlewareNames);

        return array_map(function ($name) use ($app, $route) {
            if (!array_key_exists($name, $this->globalMiddlewares)) {
                throw new UnexpectedValueException("Middleware $name not found in the configuration route settings");
            }

            $middlewareClass = $this->globalMiddlewares[$name];

            if (in_array($name, ['auth', 'grantAccess', 'requireLogin'], true)) {
                $factoryKey = "middleware.{$name}.{$route->getController()}";

                $app->factory($factoryKey, function ($app) use ($middlewareClass, $route) {
                    return $app->resolve($middlewareClass, ['route' => $route]);
                });

                return $app->resolve($factoryKey);
            }
            return $app->resolve($middlewareClass);
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
        return $controller
            ->setRequest($request)
            ->setView($app->get(ViewInterface::class))
            ->setresponse($app->getResponse())
            ->setToken($app->get(TokenInterface::class))
            ->setFlash($app->get(FlashInterface::class))
            ->setSession($app->getSession())
            ->setEventManager($app->get(EventManagerInterface::class))
            ->setBuilder($app->get(HtmlBuilder::class))
            ->setCache($app->getCache())
            ->setCookie($app->getCookie())
            ->setNavigationHistory($app->get(NavigationHistoryService::class))
            ->setRegion($app->get(RegionContextInterface::class))
            ->setTranslator($app->get(TranslatorServiceInterface::class))
            ->setSectionManager($app->get(HtmlSectionManagerInterface::class))
            ->setProviderFactory($app->get(SectionProviderFactory::class))
            ->setDecoratorFactory($app->get(DecoratorFactory::class));
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