<?php

declare(strict_types=1);

class RoutePatternConverterService
{
    public function toRegex(string $pattern, bool $namedCaptures = false): string
    {
        $regex = preg_quote($pattern, '#');

        $regex = preg_replace_callback('/\\{(\w+)(?::([^}]+))?\\}/', function ($matches) use ($namedCaptures) {
            $paramName = $matches[1];
            $paramPattern = $matches[2] ?? '[^/]+';

            if ($namedCaptures) {
                return '(?<' . $paramName . '>' . $paramPattern . ')';
            }

            return '(' . $paramPattern . ')';
        }, $regex);

        return '^' . $regex . '$';
    }

    public function toMenuMatchRegex(string $pattern): string
    {
        $route = trim($pattern, '/');
        $segments = explode('/', $route);

        $segments = array_map(function (string $segment): string {
            if (preg_match("#^\{([a-zA-Z][a-zA-Z0-9]*)\}$#", $segment, $matches)) {
                return '[^/]+';
            }
            if (preg_match("#^\{([a-zA-Z][a-zA-Z0-9]*):(.+)\}$#", $segment, $matches)) {
                $paramPattern = $matches[2];
                if ($paramPattern === '\d+') {
                    return '\d+';
                }
                if ($paramPattern === '[\w-]+') {
                    return '[\\w-]+';
                }
                return '[^/]+';
            }
            return preg_quote($segment, '#');
        }, $segments);

        $regex = '^' . implode('/', $segments);

        // Make trailing optional
        $segments = explode('/', trim($regex, '^$'));
        if (!empty($segments)) {
            $lastSegment = end($segments);
            if (str_contains($lastSegment, '(') || str_contains($lastSegment, '[') || str_contains($lastSegment, '\\d')) {
                array_pop($segments);
                $base = implode('/', $segments);
                return $base . '(?:/' . $lastSegment . ')?/?$';
            }
        }

        return $regex . '/?$';
    }

    public function toPhpRegex(string $route): string
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

    public function toJsRegex(string $pattern): string
    {
        $route = trim($pattern, '/');
        $segments = explode('/', $route);

        $segments = array_map(function (string $segment): string {
            if (preg_match("#^\{([a-zA-Z][a-zA-Z0-9]*)\}$#", $segment, $matches)) {
                return '([^/]+)';
            }
            if (preg_match("#^\{([a-zA-Z][a-zA-Z0-9]*):(.+)\}$#", $segment, $matches)) {
                return '(' . $matches[2] . ')';
            }
            return preg_quote($segment, '#');
        }, $segments);

        return '^' . implode('/', $segments) . '$';
    }

    public function extractParameters(string $pattern): array
    {
        $parameters = [];
        $route = trim($pattern, '/');

        preg_match_all("/\{([a-zA-Z][a-zA-Z0-9]*)(?::([^}]+))?\}/", $route, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $parameters[] = [
                'name' => $match[1],
                'pattern' => $match[2] ?? '[^/]+',
            ];
        }

        return $parameters;
    }

    private function makeTrailingOptional(string $regex): string
    {
        $segments = explode('/', trim($regex, '^$'));
        if (empty($segments)) {
            return $regex;
        }

        $lastSegment = end($segments);

        if (str_contains($lastSegment, '(') ||
            str_contains($lastSegment, '[') ||
            str_contains($lastSegment, '\\d')) {
            array_pop($segments);
            $base = implode('/', $segments);
            return $base . '(?:/' . $lastSegment . ')?';
        }

        return $regex;
    }
}