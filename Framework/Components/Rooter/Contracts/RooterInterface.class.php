<?php

declare(strict_types=1);

interface RooterInterface
{
    public function handle(Request $request, ?App $app = null, string|null $url = null, array $params = []) : Response;
}