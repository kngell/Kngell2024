<?php

declare(strict_types=1);
abstract class AbstractPreRequestInterceptor implements InterceptorInterface
{
    public function postRequest(Request $request, Response $response) : void
    {
    }
}