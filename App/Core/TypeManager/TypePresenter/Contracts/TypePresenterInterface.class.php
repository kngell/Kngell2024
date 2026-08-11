<?php

declare(strict_types=1);
interface TypePresenterInterface
{
    public function display(mixed $value, ?ReflectionProperty $property = null, ?RegionContextInterface $regionContext = null): mixed;

    public function supports(mixed $value, ?ReflectionProperty $property = null): bool;
}