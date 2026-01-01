<?php

declare(strict_types=1);

interface TypePresenterFactoryInterface
{
    public function getPresenterForValue(mixed $value, ?ReflectionProperty $property = null): TypePresenterInterface;

    public function getPresenterForType(string $type): ?TypePresenterInterface;

    public function displayValue(mixed $value, ?ReflectionProperty $property = null): mixed;
}