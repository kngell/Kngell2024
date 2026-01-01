<?php

declare(strict_types=1);

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

final class UuidType implements TypeHandlerInterface
{
    public function supports(mixed $value, ?ReflectionProperty $property = null): bool
    {
        return $value instanceof UuidInterface || is_string($value);
    }

    public function normalizeForDatabase(mixed $value, ?ReflectionProperty $property = null): ?string
    {
        if ($value instanceof UuidInterface) {
            return $value->toString();
        }

        if (is_string($value)) {
            return $value;
        }

        return null;
    }

    public function normalizeForEntity(mixed $value, ReflectionProperty $property, object $contextEntity): ?UuidInterface
    {
        if ($value instanceof UuidInterface) {
            return $value;
        }

        try {
            return Uuid::fromString((string) $value);
        } catch (Throwable $e) {
            throw new InvalidArgumentException('Cannot Convert the value to UUID', $e->getCode());
        }
    }
}