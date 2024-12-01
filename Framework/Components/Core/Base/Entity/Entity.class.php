<?php

declare(strict_types=1);
abstract class Entity
{
    public function table() : string
    {
        return strtolower($this::class);
    }
}