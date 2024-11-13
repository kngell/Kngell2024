<?php

declare(strict_types=1);
abstract class AbstractPostRequestInterceptor implements InterceptorInterface
{
    public function preRequest(Request $request) : void
    {
    }
}