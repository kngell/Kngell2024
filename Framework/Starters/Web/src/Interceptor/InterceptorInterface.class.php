<?php

declare(strict_types=1);

interface InterceptorInterface
{
    public function preRequest(Request $request) : void;

    public function postRequest(Request $request, Response $response) : void;

    public function order() : int;
}