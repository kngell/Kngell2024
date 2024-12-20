<?php

declare(strict_types=1);

interface MiddlewareInterface
{
    public function process(Request $request, RequestHandlerInterface $next) : Response|string;
}