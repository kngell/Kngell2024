<?php

declare(strict_types=1);

interface TypePresenterInterface
{
    public function supports(mixed $value, ?ReflectionProperty $property = null): bool;

    public function display(mixed $value, ?ReflectionProperty $property = null): mixed;
}