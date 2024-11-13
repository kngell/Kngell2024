<?php

declare(strict_types=1);

#[Service]
class CustomPreRequestInterceptor extends AbstractPreRequestInterceptor
{
    public function __construct(InterceptorChain $interceptorChain)
    {
        $interceptorChain->registerInterceptor($this);
    }

    public function preRequest(Request $request): void
    {
        $request->getHeaders()->add('X-CustomRequest-interceptor', 'This is set in the Pre REQUEST Interceptor');
    }

    public function order(): int
    {
        return 100;
    }
}