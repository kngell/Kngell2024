<?php

declare(strict_types=1);

#[Service]
class CustomPostRequestInterceptor extends AbstractPostRequestInterceptor
{
    public function __construct(InterceptorChain $interceptorChain)
    {
        $interceptorChain->registerInterceptor($this);
    }

    public function postRequest(Request $request, Response $response): void
    {
        $cutomRequestHaeder = $request->getHeaders()->get('X-CustomRequest-interceptor');
        $response->getHeaders()->add('X-Custom-interceptor', 'Custom in Response from Request: ' . $cutomRequestHaeder);
    }

    public function order(): int
    {
        return 100;
    }
}