<?php

declare(strict_types=1);

use Ramsey\Collection\Exception\InvalidPropertyOrMethod;

class RouteMatcherOLD
{
    private array $routes;
    private string $controllerSuffix = 'Controller';

    /**
     * @param array $routes
     *
     * @return void
     */
    public function __construct(RouteCollector $routeCollector, private SessionInterface $session)
    {
        $this->routes = $this->sortRoutesBySpecificity($routeCollector->getRoutes());
    }

    // public function match(Request $request, string $internalUrl): ?RouteInfo
    // {
    //     $routePath = $this->normalizeUrl($request, $internalUrl);

    //     foreach ($this->routes as $route => $params) {
    //         $pattern = $this->getPatternFromRoutePath($route);

    //         if (preg_match($pattern, $routePath, $matches)) {
    //             // Verify HTTP method if specified
    //             if (!$this->matchesHttpMethod($params, $request)) {
    //                 continue;
    //             }

    //             $matches = array_merge(array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY), $params ?? []);
    //             return $this->routeInfo($route, $pattern, $matches, $request);
    //         }
    //     }

    //     return null;
    // }
    public function match(Request $request, string $internalUrl): ?RouteInfo
    {
        $logger = LoggerFactory::create()->withRequestContext();
        $routePath = $this->normalizeUrl($request, $internalUrl);

        echo "<div style='background: orange; padding: 10px; margin: 5px;'>";
        echo '<h3>🔄 SMART ADMIN ROUTE PROCESSING</h3>';
        echo 'Testing path: ' . htmlspecialchars($routePath) . '<br>';
        echo '</div>';

        foreach ($this->routes as $routePattern => $route) {
            $pattern = $this->getPatternFromRoutePath($routePattern, $logger);
            $matchResult = preg_match($pattern, $routePath, $matches);

            if ($matchResult === 1) {
                echo "<div style='background: green; color: white; padding: 10px; margin: 5px;'>";
                echo '🎉 ROUTE MATCHED: ' . htmlspecialchars($routePattern) . '<br>';

                // Extract captured values
                $capturedValues = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

                echo '<strong>Before processing:</strong><br>';
                echo "&nbsp;&nbsp;Captured controller: '" . ($capturedValues['controller'] ?? 'empty') . "'<br>";
                echo "&nbsp;&nbsp;Captured method: '" . ($capturedValues['method'] ?? 'empty') . "'<br>";
                echo "&nbsp;&nbsp;Route controller: '" . ($route->controller ?? 'null') . "'<br>";
                echo "&nbsp;&nbsp;Route method: '" . ($route->method ?? 'null') . "'<br>";

                // Process the merge
                $finalController = $capturedValues['controller'] ?? $route->controller;
                $finalMethod = $capturedValues['method'] ?? $route->method;

                // Handle empty controller for admin routes
                // In the match method, replace this part:
                // In the match method, replace the smart processing:
                $capturedValues = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

                $extracted = $this->extractControllerAndMethod($routePattern, $capturedValues, $route);
                $finalController = $extracted['controller'];
                $finalMethod = $extracted['method'];

                echo '<strong>Route Analysis:</strong><br>';
                echo '&nbsp;&nbsp;Pattern: ' . htmlspecialchars($routePattern) . '<br>';
                echo '&nbsp;&nbsp;Is prefixed: ' . ($extracted['isPrefixed'] ? 'yes' : 'no') . '<br>';
                echo '&nbsp;&nbsp;Prefix: ' . ($extracted['prefix'] ?? 'none') . '<br>';
                echo "&nbsp;&nbsp;Final controller: '{$finalController}'<br>";
                echo "&nbsp;&nbsp;Final method: '{$finalMethod}'<br>";

                $finalParams = [
                    'controller' => $finalController,
                    'method' => $finalMethod,
                    'httpMethod' => $route->httpMethod,
                    'middleware' => $route->middleware,
                    'responseBody' => $route->responseBody,
                    'responseStatus' => $route->responseStatus,
                ];

                echo '<strong>Final result:</strong><br>';
                echo "&nbsp;&nbsp;Controller: '{$finalParams['controller']}'<br>";
                echo "&nbsp;&nbsp;Method: '{$finalParams['method']}'<br>";
                echo '</div>';

                if (!$this->matchesHttpMethod($route, $request)) {
                    continue;
                }

                return $this->routeInfo($routePattern, $pattern, $finalParams, $request);
            }
        }

        return null;
    }

    private function extractControllerAndMethod(string $routePattern, array $capturedValues, Route $route): array
    {
        $routePrefixes = ['admin', 'api']; // Configurable prefixes

        $isPrefixedRoute = false;
        $prefix = null;

        foreach ($routePrefixes as $routePrefix) {
            if (str_starts_with($routePattern, "/{$routePrefix}/")) {
                $isPrefixedRoute = true;
                $prefix = $routePrefix;
                break;
            }
        }

        $finalController = $capturedValues['controller'] ?? $route->controller;
        $finalMethod = $capturedValues['method'] ?? $route->method;

        // If no controller captured but we have a method, extract from method name
        if (empty($finalController) && !empty($capturedValues['method'])) {
            $methodParts = explode('-', $capturedValues['method']);

            if (count($methodParts) >= 2) {
                // URL like: /admin/product-add or /product-add
                $finalController = $methodParts[0]; // "product"
                $finalMethod = $methodParts[1];     // "add"
            } elseif ($isPrefixedRoute) {
                // Prefixed single word: /admin/dashboard → DashboardController->index()
                $finalController = $capturedValues['method']; // "dashboard"
                $finalMethod = 'index';
            } else {
                // Frontend single word: /contact → ContactController->index()
                $finalController = $capturedValues['method']; // "contact"
                $finalMethod = 'index';
            }
        }

        return [
            'controller' => $finalController,
            'method' => $finalMethod,
            'isPrefixed' => $isPrefixedRoute,
            'prefix' => $prefix,
        ];
    }

    private function sortRoutesBySpecificity(array $routes): array
    {
        uksort($routes, function ($a, $b) {
            return $this->calculateSpecificityScore($b) - $this->calculateSpecificityScore($a);
        });

        // Debug output
        echo "<div style='background: lightyellow; padding: 10px; margin: 5px;'>";
        echo '<h3>Route Order (Route Objects):</h3>';
        foreach (array_keys($routes) as $route) {
            $score = $this->calculateSpecificityScore($route);
            echo "• {$route} (score: {$score})<br>";
        }
        echo '</div>';

        return $routes;
    }

    private function calculateSpecificityScore(string $route): int
    {
        $score = 0;
        $segments = explode('/', trim($route, '/'));

        foreach ($segments as $segment) {
            if (str_contains($segment, '{')) {
                // Dynamic segment - slight penalty
                $score -= 5;

                // But routes with specific prefixes (like "admin/") should be higher
                if (in_array($segments[0] ?? '', ['admin', 'api', 'products'])) {
                    $score += 20; // Bonus for having specific prefix
                }
            } else {
                // Static segment - good
                $score += 15;

                // Extra bonus for common specific prefixes
                if (in_array($segment, ['admin', 'api', 'products', 'user'])) {
                    $score += 10;
                }
            }
        }

        // More segments = more specific
        $score += count($segments) * 10;

        // Routes with regex constraints are more specific
        if (str_contains($route, ':')) {
            $score += 25;
        }

        // Routes starting with specific prefixes get high priority
        if (str_starts_with($route, '/admin/')) {
            $score += 50;
        }
        if (str_starts_with($route, '/api/')) {
            $score += 40;
        }
        if (str_starts_with($route, '/products/')) {
            $score += 30;
        }

        return $score;
    }

    private function matchesHttpMethod(Route $route, Request $request): bool
    {
        if (!$route->httpMethod) {
            return true; // No HTTP method specified = matches all
        }

        $routeMethod = strtolower($route->httpMethod);
        $requestMethod = strtolower($request->getServer()->get('request_method'));

        return $routeMethod === $requestMethod;
    }

    private function calculateRouteSpecificity(Route $route): int
    {
        $path = $route->path ?? '';
        $score = 0;

        // Static paths are more specific than dynamic ones
        if (!str_contains($path, '{')) {
            $score += 100;
        }

        // Score based on path segments
        $segments = explode('/', trim($path, '/'));
        $score += count($segments) * 10;

        // Deduct points for dynamic segments
        foreach ($segments as $segment) {
            if (str_contains($segment, '{')) {
                $score -= 5;
            }
        }

        // Specific HTTP methods are more specific
        if (isset($route->httpMethod)) {
            $score += 5;
        }

        return $score;
    }

    private function routeInfo(string $path, string $pattern, array $matches, Request $request): RouteInfo
    {
        $controller = $this->controller($matches);
        $method = $this->method($controller, $matches);

        return  (new RouteInfosBuilder())
            ->withController($controller)
            ->withMethod($method)
            ->withArguments($this->getRouteArguments($method->getParameters(), $matches))
            ->withRoutePattern($pattern)
            ->withPath($this->stripPath($path))
            ->withHttpMethod($request->getMethod())
            ->withResponseBody($this->responseBody($matches))
            ->withResponseStatus($this->responseStatus($matches))
            ->withRouteParams($matches)
            ->build();
    }

    // private function routeInfo(string $path, string $pattern, array $matches, Request $request): RouteInfo
    // {
    //     $controller = $this->controller($matches);
    //     $method = $this->method($controller, $matches);
    //     return  (new RouteInfosBuilder())
    //         ->withController($controller)
    //         ->withMethod($method)
    //         ->withArguments($this->getRouteArguments($method->getParameters(), $matches))
    //         ->withRoutePattern($pattern)
    //         ->withPath($this->stripPath($path))
    //         ->withHttpMethod($request->getMethod())
    //         ->withResponseBody($this->responseBody($matches))
    //         ->withResponseStatus($this->responseStatus($matches))
    //         ->withRouteParams($matches)
    //         ->build();
    // }

    /**
     * @param ReflectionParameter[] $parameters
     *
     * @return RouteArguments[]
     */
    private function getRouteArguments(array $parameters): array
    {
        $args = [];
        foreach ($parameters as $parameter) {
            $args[] = new RouteArguments($parameter);
        }
        return $args;
    }

    private function responseBody(array $matches): ResponseBody|null
    {
        $responseBody = $matches['responseBody'] ?? null;

        if ($responseBody && is_array($responseBody)) {
            $type = strtoupper($responseBody['type'] ?? '');
            $produces = $responseBody['produces'] ?? '';

            if ($type && $produces) {
                return new ResponseBody(ResponseBodyType::from($type), $produces);
            }
        }

        return null;
    }

    private function responseStatus(array $matches): ?ResponseStatus
    {
        $responseStatus = $matches['responseStatus'] ?? null;

        if ($responseStatus && is_array($responseStatus)) {
            $statusCode = (int) ($responseStatus['HttpStatusCode'] ?? 0);
            if ($statusCode > 0) {
                return new ResponseStatus(HttpStatusCode::from($statusCode));
            }
        }

        return null;
    }

    private function normalizeUrl(Request $request, string $url): string
    {
        $url = !empty($url) ? $url : $request->getRequestedUri();
        $url = parse_url($url, PHP_URL_PATH);
        if ($url === false) {
            throw new UnexpectedValueException("Malformed url '{$request->getServer()->get('request_uri')}'");
        }

        $normalized = trim(urldecode($url), DS);

        // DEBUG: See what's being normalized
        echo "<div style='background: orange; padding: 10px; margin: 5px;'>";
        echo '<h3>URL NORMALIZATION DEBUG</h3>';
        echo 'Input URL: ' . htmlspecialchars($url) . '<br>';
        echo 'Normalized: ' . htmlspecialchars($normalized) . '<br>';
        echo "DS (Directory Separator): '" . DS . "'<br>";
        echo "Trimmed: '" . trim(urldecode($url), DS) . "'<br>";
        echo '</div>';

        return $normalized;
    }

    private function getPatternFromRoutePath(string $route, $logger): string
    {
        $route = trim($route, DS);

        // Special handling for admin routes
        if ($route === 'admin/{controller}/{method}') {
            // Smart pattern that matches both:
            // /admin/controller/method AND /admin/method
            return '#^admin(?:/(?<controller>[^/]+))?/(?<method>[^/]+)$#iu';
        }

        $segments = explode(DS, $route);
        $segments = array_map(function (string $segment): string {
            if (preg_match("#^\{([a-zA-Z][a-zA-Z0-9]*)\}$#", $segment, $matches)) {
                return '(?<' . $matches[1] . '>[^/]*)';
            }
            if (preg_match("#^\{([a-zA-Z][a-zA-Z0-9]*):(.+)\}$#", $segment, $matches)) {
                return '(?<' . $matches[1] . '>' . $matches[2] . ')';
            }
            return $segment;
        }, $segments);

        return '#^' . implode(DS, $segments) . '$#iu';
    }
    // private function getPatternFromroutePath(string $route): string
    // {
    //     $route = trim($route, DS);
    //     $segments = explode(DS, $route);
    //     $segments = array_map(function (string $segment): string {
    //         if (preg_match("#^\{([a-zA-Z][a-zA-Z0-9]*)\}$#", $segment, $matches)) {
    //             return '(?<' . $matches[1] . '>[^/]*)';
    //         }
    //         if (preg_match("#^\{([a-zA-Z][a-zA-Z0-9]*):(.+)\}$#", $segment, $matches)) {
    //             return '(?<' . $matches[1] . '>' . $matches[2] . ')';
    //         }
    //         return $segment;
    //     }, $segments);
    //     return '#^' . implode(DS, $segments) . '$#iu';
    // }

    private function controller(string $controllerName): string
    {
        $controllerSuffix = $this->controllerSuffix;

        $normalizedName = StringUtils::studlyCaps(str_replace('-', '', $controllerName));

        // Try different controller naming patterns
        $possibleControllers = [
            $normalizedName,                    // "CreateProduct"
            $controllerName,                    // "create-product" (original)
            str_replace('-', '', $controllerName), // "createproduct"
        ];

        foreach ($possibleControllers as $possibleController) {
            $fullControllerName = $possibleController . $controllerSuffix;
            if (class_exists($fullControllerName)) {
                return $fullControllerName;
            }
        }

        throw new PageNotFoundException("No controller found for: $controllerName");
    }

    private function method(string $controller, array $matches): ReflectionMethod
    {
        $methodName = $matches['method'] ?? 'index';

        echo "<div style='background: yellow; padding: 10px; margin: 5px;'>";
        echo '<h3>🔍 METHOD RESOLUTION DEBUG</h3>';
        echo 'Controller: ' . htmlspecialchars($controller) . '<br>';
        echo "Raw method from matches: '" . htmlspecialchars($methodName) . "'<br>";

        // Convert to camelCase
        $camelCaseMethod = StringUtils::camelCase($methodName);
        echo "After camelCase: '" . htmlspecialchars($camelCaseMethod) . "'<br>";

        // List available methods in the controller for debugging
        $availableMethods = get_class_methods($controller);
        echo 'Available methods in controller: ' . json_encode($availableMethods) . '<br>';

        if (method_exists($controller, $camelCaseMethod)) {
            echo '✅ Method found!<br>';
            echo '</div>';
            return new ReflectionMethod($controller, $camelCaseMethod);
        }

        echo '❌ Method not found!<br>';
        echo '</div>';
        throw new InvalidPropertyOrMethod("The Method $camelCaseMethod does not exist in controller $controller");
    }
    // private function method(string $controller, array $matches): ReflectionMethod
    // {
    //     $method = StringUtils::camelCase($matches['method']);
    //     if (method_exists($controller, $method)) {
    //         return new ReflectionMethod($controller, $method);
    //     }
    //     throw new InvalidPropertyOrMethod("The Method $method does not exist");
    // }

    /**
     * @param string $path
     *
     * @return PathElement[]
     */
    private function stripPath(string $path): array
    {
        $pathElements = [];
        foreach (explode(DS, $path) as $part) {
            if ($part !== '' && !str_contains($part, 'controller') && !str_contains($part, 'method')) {
                $builder = new PathElementBuilder();
                if (str_starts_with($part, '{') && str_ends_with($part, '}')) {
                    $part = rtrim(strtok($part, '\\'), ':') . '}';
                    $builder->withType(PathElementType::VARIABLE)
                        ->withValue(ltrim(rtrim($part, '}'), '{'));
                } else {
                    $builder->withType(PathElementType::NORMAL)
                        ->withValue($part);
                }

                $pathElements[] = $builder->build();
            }
        }
        return $pathElements;
    }
}