<?php

declare(strict_types=1);

class SecurityHeadersMiddleware implements MiddlewareInterface
{
    public function process(
        Request $request,
        RequestHandlerInterface $handler
    ): Response {
        $response = $handler->handle($request);

        // Test for missing security headers
        if (! $response->getHeaders()->has('X-Frame-Options')) {
            $response->getHeaders()->add(
                'X-Security-Header-Missing',
                'X-Frame-Options'
            );
        }

        return $response;
    }
}