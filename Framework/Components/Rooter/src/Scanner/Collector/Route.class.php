<?php

declare(strict_types=1);

final class Route
{
    public function __construct(
        public readonly string $path,
        public readonly ?string $controller,
        public readonly ?string $method,
        public readonly ?string $httpMethod = null,
        public readonly array $middleware = [],
        public readonly ?ResponseBody $responseBody = null,
        public readonly ?ResponseStatus $responseStatus = null,
    ) {
    }

    /**
     * Convert to array for RouteMatcher compatibility.
     */
    public function toArray(): array
    {
        return [
            'path' => $this->path,
            'controller' => $this->controller,
            'method' => $this->method,
            'httpMethod' => $this->httpMethod,
            'middleware' => $this->middleware,
            'responseBody' => $this->responseBody?->toArray(),
            'ResponseStatus' => $this->responseStatus?->toArray(), // Note: capital 'S' to match YAML
        ];
    }

    public function matchesMethod(mixed $httpMethod): bool
    {
        // ✅ Handle both string and HttpMethod object
        if ($httpMethod instanceof HttpMethod) {
            $httpMethod = $httpMethod->value; // Convert enum to string
        }

        if ($this->httpMethod === null) {
            return true; // No method restriction
        }

        return strtolower($this->httpMethod) === strtolower((string) $httpMethod);
    }

    /**
     * Check if route has middleware.
     */
    public function hasMiddleware(): bool
    {
        return !empty($this->middleware);
    }

    /**
     * Check if route has response body configuration.
     */
    public function hasResponseBody(): bool
    {
        return $this->responseBody !== null;
    }

    /**
     * Check if route has response status configuration.
     */
    public function hasResponseStatus(): bool
    {
        return $this->responseStatus !== null;
    }
}