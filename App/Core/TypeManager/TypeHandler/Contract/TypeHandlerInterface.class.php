<?php

declare(strict_types=1);
interface TypeHandlerInterface
{
    public function supports(mixed $value, ?ReflectionProperty $property = null): bool;

    public function normalizeForDatabase(mixed $value, ?ReflectionProperty $property = null): mixed;

    public function normalizeForEntity(mixed $value, ReflectionProperty $property, Entity $entityInstance): mixed;
}