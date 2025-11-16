<?php

declare(strict_types=1);

use Ramsey\Collection\Exception\InvalidPropertyOrMethod;

class RouteMatcher
{
    private array $routes;
    private string $controllerSuffix = 'Controller';

    /**
     * @param array $routes
     *
     * @return void
     */
    public function __construct(RouteCollector $routeCollector)
    {
        $this->routes = $routeCollector->getRouteObjects();
    }

    // public function match(Request $request, string $internalUrl): RouteInfo|null
    // {
    //     $routePath = $this->normalizeUrl($request, $internalUrl);
    //     foreach ($this->routes as $route => $params) {
    //         $pattern = $this->getPatternFromroutePath($route);
    //         if (preg_match($pattern, $routePath, $matches)) {
    //             $matches = array_merge(array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY), $params ?? []);
    //             if (array_key_exists('httpMethod', $matches)) {
    //                 $httpMethod = $request->getServer()->get('request_method');
    //                 if (strtolower($httpMethod) !== strtolower($matches['httpMethod'])) {
    //                     continue;
    //                 }
    //             }
    //             return $this->routeInfo(
    //                 $route,
    //                 $pattern,
    //                 $matches,
    //                 $request,
    //             );
    //         }
    //     }
    //     return null;
    // }

    public function match(Request $request, string $internalUrl): RouteInfo|null
    {
        try {
            $routePath = $this->normalizeUrl($request, $internalUrl);

            foreach ($this->routes as $routePathKey => $route) {
                // $routePathKey is the configured route path (e.g. '/{controller}/{method}')
                $pattern = $this->getPatternFromroutePath($routePathKey);

                if (!preg_match($pattern, $routePath, $rawMatches)) {
                    continue;
                }

                // Keep only named captures (associative keys)
                $namedMatches = array_filter($rawMatches, 'is_string', ARRAY_FILTER_USE_KEY);

                // Determine controller and method: prefer explicit values from the Route object,
                // otherwise fall back to the named captures from the URL.
                $controller = $route->controller ?? ($namedMatches['controller'] ?? null);
                $method = $route->method ?? ($namedMatches['method'] ?? null);

                // Merge route properties with captured params. Named captures should override
                // route defaults when present.
                $routeParams = method_exists($route, 'toArray') ? $route->toArray() : [];
                $mergedMatches = array_merge($routeParams, $namedMatches);

                // Ensure controller/method are present in merged matches for downstream use
                if ($controller !== null) {
                    $mergedMatches['controller'] = $controller;
                }
                if ($method !== null) {
                    $mergedMatches['method'] = $method;
                }

                // Check HTTP method on the Route object
                if (method_exists($route, 'matchesMethod') && !$route->matchesMethod($request->getMethod())) {
                    continue;
                }

                return $this->routeInfo($route, $mergedMatches, $request);
            }

            return null;
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Analyze why a pattern doesn't match a URL.
     */
    private function analyzePatternMatch(string $pattern, string $url): void
    {
        // Debug helper removed
    }

    private function routeInfo(Route $route, array $matches, Request $request): RouteInfo
    {
        $controller = $this->controller($route->controller, $matches);
        $method = $this->method($controller, $route->method, $matches);

        return (new RouteInfosBuilder())
            ->withController($controller)
            ->withMethod($method)
            ->withArguments($this->getRouteArguments($method->getParameters(), $matches))
            ->withRoutePattern($this->getPatternFromroutePath($route->path))
            ->withPath($this->stripPath($route->path))
            ->withHttpMethod($request->getMethod())
            ->withResponseBody($route->responseBody)
            ->withResponseStatus($route->responseStatus)
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
        foreach ($matches as $key => $value) {
            if (strtolower($key) === 'responsebody') {
                $type = strtoupper($value['type']);
                $produces = $value['produces'];
                return new ResponseBody(ResponseBodyType::from($type), $produces);
            }
        }
        return null;
    }

    private function responseStatus(array $matches): ?ResponseStatus
    {
        foreach ($matches as $key => $value) {
            if (strtolower($key) === 'responsestatus') {
                $statusCode = (int) $value;
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
        return trim(urldecode($url), '/');
    }

    private function getPatternFromroutePath(string $route): string
    {
        $route = trim($route, '/');
        $segments = explode('/', $route);

        $segments = array_map(function (string $segment): string {
            // Match {param} patterns
            if (preg_match("#^\{([a-zA-Z][a-zA-Z0-9]*)\}$#", $segment, $matches)) {
                return '(?<' . $matches[1] . '>[^/]+)';
            }
            // Match {param:regex} patterns
            if (preg_match("#^\{([a-zA-Z][a-zA-Z0-9]*):(.+)\}$#", $segment, $matches)) {
                return '(?<' . $matches[1] . '>' . $matches[2] . ')';
            }
            return preg_quote($segment, '#');
        }, $segments);

        return '#^' . implode('/', $segments) . '$#iu';
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

    private function controller(?string $controller, array $matches): string
    {
        $controller = StringUtils::studlyCaps($controller ?? $matches['controller']);
        if (class_exists($controller . $this->controllerSuffix)) {
            return $controller . $this->controllerSuffix;
        }
        throw new PageNotFoundException("Page $controller not Found");
    }

    private function method(string $controller, ?string $method, array $matches): ReflectionMethod
    {
        $methodName = $method ?? ($matches['method'] ?? null);

        if ($methodName === null) {
            throw new InvalidPropertyOrMethod("No method specified for controller $controller");
        }

        $methodName = StringUtils::camelCase($methodName);
        if (method_exists($controller, $methodName)) {
            return new ReflectionMethod($controller, $methodName);
        }
        throw new InvalidPropertyOrMethod("The Method $methodName does not exist");
    }

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