<?php

declare(strict_types=1);

use Ramsey\Collection\Exception\InvalidPropertyOrMethod;

final class RouteMatcher
{
    private string $controllerSuffix = 'Controller';

    public function __construct(
        private RouteMatchingService $matchingService,
        private ParameterAliasRegistry $aliasses,
    ) {
    }

    public function match(Request $request, string $internalUrl): RouteInfo|null
    {
        try {
            $routePath = $this->normalizeUrl($request, $internalUrl);
            $routes = $this->matchingService->getRoutes();

            $routeInfo = $this->matchingService->findRouteForPath($routePath, $routes);

            if (!$routeInfo) {
                return null;
            }

            $route = $routeInfo['route'];
            $matches = $routeInfo['matches'];

            // Check HTTP method
            if (method_exists($route, 'matchesMethod') && !$route->matchesMethod($request->getMethod())) {
                return null;
            }

            return $this->buildRouteInfo($route, $matches, $request);
        } catch (Exception $e) {
            return null;
        }
    }

    private function buildRouteInfo(Route $route, array $matches, Request $request): RouteInfo
    {
        $controller = $this->controller($route->controller, $matches);
        $method = $this->method($controller, $route->method, $matches);

        $responseBody = $this->extractResponseBodyAttribute($method);
        $responseStatus = $this->extractResponseStatusAttribute($method);

        if ($responseBody === null) {
            $responseBody = $this->responseBodyFromConfig($matches);
        }
        if ($responseStatus === null) {
            $responseStatus = $this->responseStatusFromConfig($matches);
        }

        return (new RouteInfosBuilder())
            ->withController($controller)
            ->withMethod($method)
            ->withArguments($this->getRouteArguments($method->getParameters()))
            ->withRoutePattern($this->getPatternFromroutePath($route->path))
            ->withPath($this->stripPath($route->path))
            ->withHttpMethod($request->getMethod())
            ->withResponseBody($responseBody)
            ->withResponseStatus($responseStatus)
            ->withRouteParams($matches)
            ->build();
    }

    private function extractResponseBodyAttribute(ReflectionMethod $method): ResponseBody|null
    {
        $attributes = $method->getAttributes(ResponseBody::class);

        if (!empty($attributes)) {
            return $attributes[0]->newInstance();
        }

        return null;
    }

    private function extractResponseStatusAttribute(ReflectionMethod $method): ResponseStatus|null
    {
        $attributes = $method->getAttributes(ResponseStatus::class);

        if (!empty($attributes)) {
            return $attributes[0]->newInstance();
        }

        return null;
    }

    private function responseBodyFromConfig(array $matches): ResponseBody|null
    {
        foreach ($matches as $key => $value) {
            if (strtolower($key) === 'responsebody' && isset($value['type'])) {
                $type = strtoupper($value['type']);
                $produces = $value['produces'];
                return new ResponseBody(ResponseBodyType::from($type), $produces);
            }
        }
        return null;
    }

    private function responseStatusFromConfig(array $matches): ?ResponseStatus
    {
        foreach ($matches as $key => $value) {
            if (strtolower($key) === 'responsestatus' && isset($value['HttpStatusCode'])) {
                $statusCode = (int) $value;
                return new ResponseStatus(HttpStatusCode::from($statusCode));
            }
        }
        return null;
    }

    /**
     * @param ReflectionParameter[] $parameters
     *
     * @return RouteArguments[]
     */
    private function getRouteArguments(array $parameters): array
    {
        $args = [];
        foreach ($parameters as $parameter) {
            $args[] = new RouteArguments($parameter, $this->aliasses);
        }
        return $args;
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

    private function controller(?string $controller, array $matches): string
    {
        $controller = StringUtils::studlyCaps($controller ?? $matches['controller']) . $this->controllerSuffix;
        if (class_exists($controller)) {
            return $controller;
        }
        throw new PageNotFoundException("Page $controller not Found");
    }

    private function method(string $controller, ?string $method, array $matches): ReflectionMethod
    {
        $methodName = $method ?? ($matches['method'] ?? null);

        if ($methodName === null) {
            throw new InvalidPropertyOrMethod("No method specified for controller $controller");
        }

        $methodName = StringUtils::snakeCaseToCamelCase($methodName);
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