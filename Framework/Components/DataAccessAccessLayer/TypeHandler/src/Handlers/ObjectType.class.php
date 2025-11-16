<?php

declare(strict_types=1);

class ObjectType implements TypeHandlerInterface
{
    public function supports(mixed $value, ?ReflectionProperty $property = null): bool
    {
        return is_object($value) || ($property && class_exists($property->getType()?->getName() ?? ''));
    }

    public function normalizeForDatabase(mixed $value): mixed
    {
        if (method_exists($value, '__toString')) {
            return (string) $value;
        }

        if (method_exists($value, 'toArray')) {
            return json_encode($value->toArray(), JSON_UNESCAPED_UNICODE);
        }

        throw new UnsupportedValueTypeException(
            sprintf('Object of type %s cannot be normalized for database', get_class($value)),
        );
    }

    public function normalizeForEntity(
        mixed $value,
        ReflectionProperty $property,
        object $contextEntity,
    ): mixed {
        $targetClass = $property->getType()?->getName();

        if (is_object($value)) {
            return $value;
        }

        if (!is_string($value) || !$targetClass || !class_exists($targetClass)) {
            throw new InvalidArgumentException(
                sprintf('Cannot denormalize scalar into object of type %s.', $targetClass ?? 'unknown'),
            );
        }

        $decoded = json_decode($value, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            if (method_exists($targetClass, 'fromArray')) {
                return $targetClass::fromArray($decoded);
            }
        }

        if (is_scalar($value)) {
            try {
                return new $targetClass($value);
            } catch (Throwable $e) {
                // Suppress exception to try the final throw
            }
        }

        // Final fail
        throw new InvalidArgumentException(
            sprintf('Failed to denormalize raw value into object of type %s. Check for missing ::fromArray() or suitable constructor.', $targetClass),
        );
    }
}