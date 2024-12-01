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

    public function match(Request $request, string $internalUrl = '') : RouteInfo|null
    {
        $routePath = $this->getRoutePath($request, $internalUrl);
        foreach ($this->routes as $route => $params) {
            $pattern = $this->getPatternFromroutePath($route);
            if (preg_match($pattern, $routePath, $matches)) {
                $matches = array_merge(array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY), $params ?? []);
                return $this->route($route, $pattern, $matches);
            }
        }
        return null;
    }

    private function getRoutePath(Request $request, string $url) : string
    {
        $url = ! empty($url) ? $url : $request->getRequestedUri();
        $url = parse_url($url, PHP_URL_PATH);
        if ($url === false) {
            throw new UnexpectedValueException("Malformed url '{$request->_Server('request_uri')}'");
        }
        return trim(urldecode($url), DS);
    }

    private function route(string $path, string $pattern, array $matches) : RouteInfo
    {
        $controller = $this->controller($matches);
        $method = $this->method($controller, $matches);
        return  (new RouteInfosBuilder())->withController($controller)
            ->withMethod($method)
            ->withArguments($this->getRouteArguments($method->getParameters(), $matches))
            ->withRoutePattern($pattern)
            ->withPath($this->stripPath($path))
            ->withHttpMethod(HttpMethod::fromString($_SERVER['REQUEST_METHOD']))
            ->withResponseBody(new ResponseBody(ResponseBodyType::RAW, 'text/xml'))
            ->withResponseStatus(new ResponseStatus(HttpStatusCode::HTTP_CREATED))
            ->withRouteParams($matches)
            ->build();
    }

    private function getPatternFromroutePath(string $route) : string
    {
        $route = trim($route, DS);
        $segments = explode(DS, $route);
        $segments = array_map(function (string $segment) : string {
            if (preg_match("#^\{([a-z][a-z0-9]*)\}$#", $segment, $matches)) {
                return '(?<' . $matches[1] . '>[^/]*)';
            }
            if (preg_match("#^\{([a-z][a-z0-9]*):(.+)\}$#", $segment, $matches)) {
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
     * @param ReflectionParameter[] $parameters
     * @param array $arguments
     * @return array
     */
    private function getRouteArguments(array $parameters, array $arguments) : array
    {
        $args = [];
        foreach ($parameters as $parameter) {
            $name = $parameter->getName();
            if (array_key_exists($name, $arguments)) {
                $args[] = new RouteArguments($name, $arguments[$name]);
            }
        }
        return $args;
    }

    /**
     * @param string $path
     * @return PathElement[]
     */
    private function stripPath(string $path) : array
    {
        $pathElements = [];
        foreach (explode(DS, $path) as $part) {
            $builder = new PathElementBuilder();
            if (str_starts_with($part, '{') && str_ends_with($part, '}')) {
                $builder->withType(PathElementType::VARIABLE)
                    ->withValue(ltrim(rtrim($part, '}'), '{'));
            } else {
                $builder->withType(PathElementType::NORMAL)
                    ->withValue($part);
            }

            $pathElements[] = $builder->build();
        }
        return $pathElements;
    }
}