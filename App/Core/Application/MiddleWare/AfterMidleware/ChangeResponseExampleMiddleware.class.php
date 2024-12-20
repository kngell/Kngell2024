<?php

declare(strict_types=1);

class ChangeResponseExampleMiddleware implements MiddlewareInterface
{
    public function process(Request $request, RequestHandlerInterface $next) : Response|string
    {
        /** @var Response */
        $response = $next->handle($request);
        if (is_string($response)) {
            return $response . 'Hello from the midleware';
        }
        $response->setContent($response->getContent() . 'Hello from the midleware');
        return $response;
    }
}