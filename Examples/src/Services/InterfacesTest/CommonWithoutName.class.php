<?php

declare(strict_types=1);

#[Service]
#[Primary]
class CommonImplWithoutName implements CommonInterface
{
    public function handle(): string
    {
        return 'world';
    }
}