<?php

declare(strict_types=1);
class InterceptorChain
{
    /**
     * @var InterceptorInterface[]
     */
    private array $interceptors = [];

    public function registerInterceptor(InterceptorInterface $interceptor) : void
    {
        $this->interceptors[] = $interceptor;
        usort(
            $this->interceptors,
            static fn (InterceptorInterface $a, InterceptorInterface $b) => $a->order() - $b->order()
        );
    }

    public function preRequest(Request $request) : void
    {
        foreach ($this->interceptors as $interceptor) {
            $interceptor->preRequest($request);
        }
    }

    public function postRequest(Request $request, Response $response) : void
    {
        foreach ($this->interceptors as $interceptor) {
            $interceptor->postRequest($request, $response);
        }
    }
}