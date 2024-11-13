<?php

declare(strict_types=1);
class RouteInfoBuilder
{
    private string|null $routeInfoId = null;
    private BeanInfo $controller;
    private HttpMethod $httpMethod;

    /** @var PathElement[] */
    private array $path = [];

    /** @var RouteArgument[] */
    private array $arguments = [];

    private ResponseBody|null $responseBody;
    private ResponseStatus|null $responseStatus;
    private ReflectionNamedType $returnType;
    private ReflectionMethod $method;

    /**
     * @param string|null $routeInfoId
     * @return RouteInfoBuilder
     */
    public function withRouteInfoId(?string $routeInfoId): self
    {
        $this->routeInfoId = $routeInfoId;
        return $this;
    }

    /**
     * @param BeanInfo $controller
     * @return self
     */
    public function withController(BeanInfo $controller): self
    {
        $this->controller = $controller;
        return $this;
    }

    /**
     * @param HttpMethod $httpMethod
     * @return self
     */
    public function withHttpMethod(HttpMethod $httpMethod): self
    {
        $this->httpMethod = $httpMethod;
        return $this;
    }

    /**
     * @param PathElement[] $path
     * @return self
     */
    public function withPath(array $path): self
    {
        $this->path = $path;
        return $this;
    }

    /**
     * @param RouteArgument[] $arguments
     * @return self
     */
    public function withArguments(array $arguments): self
    {
        $this->arguments = $arguments;
        return $this;
    }

    /**
     * @param ResponseBody|null $responseBody
     * @return self
     */
    public function withResponseBody(?ResponseBody $responseBody): self
    {
        $this->responseBody = $responseBody;
        return $this;
    }

    /**
     * @param ResponseStatus|null $responseStatus
     * @return self
     */
    public function withResponseStatus(?ResponseStatus $responseStatus): self
    {
        $this->responseStatus = $responseStatus;
        return $this;
    }

    /**
     * @param ReflectionNamedType $returnType
     * @return self
     */
    public function withReturnType(ReflectionNamedType $returnType): self
    {
        $this->returnType = $returnType;
        return $this;
    }

    /**
     * @param ReflectionMethod $method
     * @return self
     */
    public function withMethod(ReflectionMethod $method): self
    {
        $this->method = $method;
        return $this;
    }

    public function build() : RouteInfo
    {
        return new RouteInfo(
            $this->controller,
            $this->httpMethod,
            $this->path,
            $this->arguments,
            $this->responseBody,
            $this->responseStatus,
            $this->returnType,
            $this->method,
            $this->routeInfoId
        );
    }
}