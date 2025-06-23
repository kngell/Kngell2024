<?php

declare(strict_types=1);

class ChangeRequestExampleMiddleware implements MiddlewareInterface
{
    public function __construct(private Token $token)
    {
    }

    public function process(Request $request, RequestHandlerInterface $next) : Response|string
    {
        $request->getPost()->addAll((array_map('trim', $request->getPost()->getAll())));
        /* @var Response */
        return $next->handle($request);
    }
}