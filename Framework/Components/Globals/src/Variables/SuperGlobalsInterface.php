<?php

declare(strict_types=1);

interface SuperGlobalsInterface
{
    public function request(?string $key = null) : mixed;

    public function get(?string $key = null): mixed;

    public function post(?string $key = null): mixed;

    public function cookies(?string $key = null): mixed;

    public function files(?string $key = null): mixed;

    public function server(?string $key = null): mixed;

    public function emptyGlobals() : void;
}
