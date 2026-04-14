<?php

declare(strict_types=1);

class AdminGroupMiddleware implements MiddlewareInterface
{
    public function __construct(
    ) {
    }

    public function process(Request $request, RequestHandlerInterface $next): Response|string
    {
        return $next->handle($request);
    }
}