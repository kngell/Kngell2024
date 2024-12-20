<?php

declare(strict_types=1);

interface RequestHandlerInterface
{
    public function handle(Request $request) : string|Response;
}