<?php

declare(strict_types=1);

use Ramsey\Uuid\Uuid;

readonly class RouteInfo
{
    private string $routeInfoId;
    private BeanInfo $controller;
    private HttpMethod $httpMethod;

    /** @var PathElement[] */
    private array $path;
    /** @var RouteArgument[] */
    private array $arguments;
    private ResponseBody|null $responseBody;
    private ResponseStatus|null $responseStatus;
    private ReflectionNamedType $returnType;
    private ReflectionMethod $method;

    /**
     * @param BeanInfo $controller
     * @param HttpMethod $httpMethod
     * @param PathElement[] $path
     * @param RouteArgument[] $arguments
     * @param ResponseBody|null $responseBody
     * @param ResponseStatus|null $responseStatus
     * @param ReflectionNamedType $returnType
     * @param ReflectionMethod $method
     */
    public function __construct(
        BeanInfo $controller,
        HttpMethod $httpMethod,
        array $path,
        array $arguments,
        ?ResponseBody $responseBody,
        ?ResponseStatus $responseStatus,
        ReflectionNamedType $returnType,
        ReflectionMethod $method,
        ?string $routeInfoId = null
    ) {
        $this->routeInfoId = $routeInfoId ?? Uuid::uuid4()->toString();
        $this->controller = $controller;
        $this->httpMethod = $httpMethod;
        $this->path = $path;
        $this->arguments = $arguments;
        $this->responseBody = $responseBody;
        $this->responseStatus = $responseStatus;
        $this->returnType = $returnType;
        $this->method = $method;
    }

    /**
     * @return string
     */
    public function getRouteInfoId(): string
    {
        return $this->routeInfoId;
    }

    /**
     * @return BeanInfo
     */
    public function getController(): BeanInfo
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
     * @return PathElement[]
     */
    public function getPath(): array
    {
        return $this->path;
    }

    /**
     * @return RouteArgument[]
     */
    public function getArguments(): array
    {
        return $this->arguments;
    }

    /**
     * @return ResponseBody|null
     */
    public function getResponseBody(): ?ResponseBody
    {
        return $this->responseBody;
    }

    /**
     * @return ResponseStatus|null
     */
    public function getResponseStatus(): ?ResponseStatus
    {
        return $this->responseStatus;
    }

    /**
     * @return ReflectionNamedType
     */
    public function getReturnType(): ReflectionNamedType
    {
        return $this->returnType;
    }

    /**
     * @return ReflectionMethod
     */
    public function getMethod(): ReflectionMethod
    {
        return $this->method;
    }
}
