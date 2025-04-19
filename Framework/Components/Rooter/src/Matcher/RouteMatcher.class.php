<?php

declare(strict_types=1);

use Ramsey\Collection\Exception\InvalidPropertyOrMethod;

class RouteMatcher
{
    private array $routes;
    private string $controllerSuffix = 'Controller';

    /**
     * @param array $routes
     * @return void
     */
    public function __construct(RouteCollector $routeCollector)
    {
        $this->routes = $routeCollector->getRoutes();
    }

    public function match(Request $request, string $internalUrl) : RouteInfo|null
    {
        $routePath = $this->normalizeUrl($request, $internalUrl);
        foreach ($this->routes as $route => $params) {
            $pattern = $this->getPatternFromroutePath($route);
            if (preg_match($pattern, $routePath, $matches)) {
                $matches = array_merge(array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY), $params ?? []);
                if (array_key_exists('httpMethod', $matches)) {
                    $httpMethod = $request->getServer()->get('request_method');
                    if (strtolower($httpMethod) !== strtolower($matches['httpMethod'])) {
                        continue;
                    }
                }
                return $this->routeInfo(
                    $route,
                    $pattern,
                    $matches,
                    $request
                );
            }
        }
        return null;
    }

    private function routeInfo(string $path, string $pattern, array $matches, Request $request) : RouteInfo
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

    /**
     * @param ReflectionParameter[] $parameters
     * @return RouteArguments[]
     */
    private function getRouteArguments(array $parameters) : array
    {
        $args = [];
        foreach ($parameters as $parameter) {
            $args[] = new RouteArguments($parameter);
        }
        return $args;
    }

    private function responseBody(array $matches) : ResponseBody|null
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

    private function responseStatus(array $matches) : ?ResponseStatus
    {
        foreach ($matches as $key => $value) {
            if (strtolower($key) === 'responsestatus') {
                $statusCode = (int) $value;
                return new ResponseStatus(HttpStatusCode::from($statusCode));
            }
        }
        return null;
    }

    private function normalizeUrl(Request $request, string $url) : string
    {
        $url = ! empty($url) ? $url : $request->getRequestedUri();
        $url = parse_url($url, PHP_URL_PATH);
        if ($url === false) {
            throw new UnexpectedValueException("Malformed url '{$request->getServer()->get('request_uri')}'");
        }
        return trim(urldecode($url), DS);
    }

    private function getPatternFromroutePath(string $route) : string
    {
        $route = trim($route, DS);
        $segments = explode(DS, $route);
        $segments = array_map(function (string $segment) : string {
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

    private function controller(array $matches) : string
    {
        $controller = StringUtils::studlyCaps($matches['controller']);
        if (class_exists($controller . $this->controllerSuffix)) {
            return $controller . $this->controllerSuffix;
        }
        throw new PageNotFoundException("Page $controller not Found");
    }

    private function method(string $controller, array $matches) : ReflectionMethod
    {
        $method = StringUtils::camelCase($matches['method']);
        if (method_exists($controller, $method)) {
            return new ReflectionMethod($controller, $method);
        }
        throw new InvalidPropertyOrMethod("The Method $method does not exist");
    }

    /**
     * @param string $path
     * @return PathElement[]
     */
    private function stripPath(string $path) : array
    {
        $pathElements = [];
        foreach (explode(DS, $path) as $part) {
            if ($part !== '' && ! str_contains($part, 'controller') && ! str_contains($part, 'method')) {
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