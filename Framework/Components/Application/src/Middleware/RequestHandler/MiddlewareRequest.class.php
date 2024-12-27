<?php

declare(strict_types=1);
class MiddlewareRequest implements RequestHandlerInterface
{
    /**
     * @param MiddlewareInterface[] $middlewares
     * @param RequestHandlerInterface $requestHandler
     * @return void
     */
    public function __construct(private array $middlewares, private RequestHandlerInterface $requestHandler)
    {
    }

    public function handle(Request $request, array $args = []): string|Response
    {
        $middleware = array_shift($this->middlewares);
        if ($middleware === null) {
            return $this->requestHandler->handle($request, $args);
        }
        return $middleware->process($request, $this);
    }
}