<?php

declare(strict_types=1);

#[Service('CommonWithName')]
class CommonImplWithName implements CommonInterface
{
    public function handle(): string
    {
        return 'withName';
    }
}