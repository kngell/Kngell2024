<?php

declare(strict_types=1);

trait RuntimeConfigurableTrait
{
    public function configure(array $params): static
    {
        $reflection = CustomReflection::getInstance($this)->getClass();

        foreach ($params as $key => $value) {
            if (!$reflection->hasProperty($key)) {
                throw new InvalidArgumentException(
                    sprintf(
                        '%s does not have a configurable property "%s".',
                        static::class,
                        $key,
                    ),
                );
            }

            $property = $reflection->getProperty($key);

            if ($property->isReadOnly()) {
                throw new InvalidArgumentException(
                    sprintf(
                        'Property "%s" on %s is readonly.',
                        $key,
                        static::class,
                    ),
                );
            }

            $type = $property->getType();

            if ($type !== null && !$this->isValueCompatible($type, $value)) {
                throw new InvalidArgumentException(
                    sprintf(
                        'Property "%s" on %s expects %s, got %s.',
                        $key,
                        static::class,
                        $type,
                        get_debug_type($value),
                    ),
                );
            }

            $this->{$key} = $value;
        }

        return $this;
    }

    private function isValueCompatible(ReflectionType $type, mixed $value): bool
    {
        if ($type instanceof ReflectionNamedType) {
            return $this->matchesNamedType($type, $value);
        }

        // Union type: array|Entity — at least one must match
        if ($type instanceof ReflectionUnionType) {
            foreach ($type->getTypes() as $memberType) {
                if ($memberType instanceof ReflectionNamedType && $this->matchesNamedType($memberType, $value)) {
                    return true;
                }
            }
            return false;
        }

        // Intersection types or unknown — skip validation
        return true;
    }

    private function matchesNamedType(ReflectionNamedType $type, mixed $value): bool
    {
        if ($type->allowsNull() && $value === null) {
            return true;
        }

        $name = $type->getName();

        if ($type->isBuiltin()) {
            return match ($name) {
                'int' => is_int($value),
                'float' => is_float($value) || is_int($value),
                'string' => is_string($value),
                'bool' => is_bool($value),
                'array' => is_array($value),
                'mixed' => true,
                default => true,
            };
        }

        return $value instanceof $name;
    }
}