<?php

declare(strict_types=1);

#[Service]
class CommonImplHello implements CommonInterface
{
    public function handle(): string
    {
        return 'hello';
    }
}