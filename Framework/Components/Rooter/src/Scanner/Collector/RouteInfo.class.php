<?php

declare(strict_types=1);

use Ramsey\Uuid\Uuid;

readonly class RouteInfo
{
    private string $routeInfoId;
    private string $name;
    private string|null $controller;
    private string $routePattern;
    private HttpMethod|null $httpMethod;

    /** @var PathElement[] */
    private array $path;

    /** @var RouteArgument[] */
    private array $arguments;

    private ResponseBody|null $responseBody;
    private ResponseStatus|null $responseStatus;

    private ?ReflectionNamedType $returnType;

    private array $routeParams;

    private ReflectionMethod|null $method;

    public function __construct(
        string|null $controller,
        string $routePattern,
        HttpMethod|null $httpMethod,
        ResponseBody|null $responseBody,
        ResponseStatus|null $responseStatus,
        array $path,
        string $name,
        array $arguments,
        ReflectionMethod|null $method,
        array $routeParams,
        ?ReflectionNamedType $returnType = null,
        ?string $routeInfoId = null,
    ) {
        $this->routeInfoId = $routeInfoId ?? Uuid::uuid4()->toString();
        $this->controller = $controller;
        $this->routePattern = $routePattern;
        $this->httpMethod = $httpMethod;
        $this->responseBody = $responseBody;
        $this->responseStatus = $responseStatus;
        $this->path = $path;
        $this->name = $name;
        $this->routeParams = $routeParams;
        $this->arguments = $arguments;
        $this->method = $method;
        $this->returnType = $returnType;
    }

    /**
     * @return string
     */
    public function getRouteInfoId(): string
    {
        return $this->routeInfoId;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getController(): string
    {
        return $this->controller;
    }

    /**
     * @return HttpMethod
     */
    public function getHttpMethod(): HttpMethod
    {
        return $this->httpMethod;
    }

    /**
     * @return array
     */
    public function getPath(): array
    {
        return $this->path;
    }

    /**
     * @return RouteArguments[]
     */
    public function getArguments(): array
    {
        return $this->arguments;
    }

    /**
     * @return ReflectionMethod
     */
    public function getMethod(): ReflectionMethod
    {
        return $this->method;
    }

    /**
     * Get the value of routePattern.
     *
     * @return string
     */
    public function getRoutePattern(): string
    {
        return $this->routePattern;
    }

    /**
     * Get the value of routeParams.
     *
     * @return array
     */
    public function getRouteParams(): array
    {
        return $this->routeParams;
    }

    /**
     * Get the value of responseBody.
     *
     * @return ResponseBody|null
     */
    public function getResponseBody(): ResponseBody|null
    {
        return $this->responseBody;
    }

    /**
     * Get the value of responseStatus.
     *
     * @return ResponseStatus|null
     */
    public function getResponseStatus(): ResponseStatus|null
    {
        return $this->responseStatus;
    }

    /**
     * Get the value of returnType.
     *
     * @return ReflectionNamedType
     */
    public function getReturnType(): ReflectionNamedType
    {
        return $this->returnType;
    }
}
