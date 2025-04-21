<?php

declare(strict_types=1);
class RouteInfosBuilder
{
    private string|null $routeInfoId = null;
    private string $name = '';
    private string|null $controller = null;
    private string $routePattern;
    private HttpMethod|null $httpMethod = null;
    private ResponseBody|null $responseBody;
    private ResponseStatus|null $responseStatus;

    /** @var PathElement[] */
    private array $path = [];

    /** @var RouteArguments[] */
    private array $arguments = [];

    private array $routeParams = [];

    private ReflectionMethod|null $method = null;
    private ?ReflectionNamedType $returnType = null;

    /**
     * Set the value of routeInfoId.
     *
     * @param string|null $routeInfoId
     *
     * @return self
     */
    public function withRouteInfoId(string|null $routeInfoId): self
    {
        $this->routeInfoId = $routeInfoId;
        return $this;
    }

    /**
     * Set the value of controller.
     *
     * @param string $controller
     *
     * @return self
     */
    public function withController(string $controller): self
    {
        $this->controller = $controller;
        return $this;
    }

    /**
     * Set the value of httpMethod.
     *
     * @param HttpMethod $httpMethod
     *
     * @return self
     */
    public function withHttpMethod(HttpMethod $httpMethod): self
    {
        $this->httpMethod = $httpMethod;
        return $this;
    }

    /**
     * Set the value of path.
     *
     * @param PathElement[] $path
     *
     * @return self
     */
    public function withPath(array $path): self
    {
        $this->path = $path;
        return $this;
    }

    /**
     * Set the value of arguments.
     *
     * @param RouteArguments[] $arguments
     *
     * @return self
     */
    public function withArguments(array $arguments): self
    {
        $this->arguments = $arguments;
        return $this;
    }

    /**
     * Set the value of method.
     *
     * @param ReflectionMethod $method
     *
     * @return self
     */
    public function withMethod(ReflectionMethod $method): self
    {
        $this->method = $method;
        return $this;
    }

    /**
     * Set the value of name.
     *
     * @param string $name
     *
     * @return self
     */
    public function withName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Set the value of routePattern.
     *
     * @param string $routePattern
     *
     * @return self
     */
    public function withRoutePattern(string $routePattern): self
    {
        $this->routePattern = $routePattern;
        return $this;
    }

    /**
     * Set the value of routeParams.
     *
     * @param array $routeParams
     *
     * @return self
     */
    public function withRouteParams(array $routeParams): self
    {
        $this->routeParams = $routeParams;
        return $this;
    }

    /**
     * Set the value of responseBody.
     *
     * @param ResponseBody|null $responseBody
     *
     * @return self
     */
    public function withResponseBody(ResponseBody|null $responseBody): self
    {
        $this->responseBody = $responseBody;
        return $this;
    }

    /**
     * Set the value of responseStatus.
     *
     * @param ResponseStatus|null $responseStatus
     *
     * @return self
     */
    public function withResponseStatus(ResponseStatus|null $responseStatus): self
    {
        $this->responseStatus = $responseStatus;
        return $this;
    }

    /**
     * Set the value of returnType.
     *
     * @param ?ReflectionNamedType $returnType
     *
     * @return self
     */
    public function withReturnType(?ReflectionNamedType $returnType): self
    {
        $this->returnType = $returnType;

        return $this;
    }

    public function build() : RouteInfo
    {
        return new RouteInfo(
            $this->controller,
            $this->routePattern,
            $this->httpMethod,
            $this->responseBody,
            $this->responseStatus,
            $this->path,
            $this->name,
            $this->arguments,
            $this->method,
            $this->routeParams,
            $this->returnType,
            $this->routeInfoId,
        );
    }
}