<?php

declare(strict_types=1);

class ChangeTracker implements ChangeTrackerInterface
{
    private array $originalData = [];

    public function __construct()
    {
    }

    public function track(Entity $entity): void
    {
        $hash = $this->getEntityHash($entity);

        if (isset($this->originalData[$hash])) {
            return;
        }

        $this->originalData[$hash] = $entity->toArray();
    }

    public function stopTracking(Entity $entity): void
    {
        $hash = $this->getEntityHash($entity);
        unset($this->originalData[$hash]);
    }

    public function getOriginalData(Entity $entity): array
    {
        $hash = $this->getEntityHash($entity);
        return $this->originalData[$hash] ?? [];
    }

    public function getChanges(Entity $entity): array
    {
        $hash = $this->getEntityHash($entity);

        if (!isset($this->originalData[$hash])) {
            return [];
        }

        $original = $this->originalData[$hash];
        $current = $entity->toArray();
        $changes = [];

        foreach ($current as $key => $currentValue) {
            $originalValue = $original[$key] ?? null;
            if ($this->hasValueChanged($currentValue, $originalValue, $entity, $key)) {
                $changes[$key] = $currentValue;
            }
        }

        return $changes;
    }

    public function clear(Entity $entity): void
    {
        $hash = $this->getEntityHash($entity);
        unset($this->originalData[$hash]);
    }

    public function hasChanges(Entity $entity): bool
    {
        return count($this->getChanges($entity)) > 0;
    }

    public function hasChanged(Entity $entity): bool
    {
        $hash = $this->getEntityHash($entity);
        return isset($this->originalData[$hash]) && count($this->getChanges($entity)) > 0;
    }

    private function getEntityHash(Entity $entity): string
    {
        if ($entity->entityKeyIsInitialzed()) {
            $idValue = $entity->getEntityPrimarykeyValue();
            return get_class($entity) . ':' . $idValue;
        }

        return spl_object_hash($entity);
    }

    private function hasValueChanged(mixed $currentValue, mixed $originalValue, Entity $entity, string $key): bool
    {
        if ($currentValue === $originalValue) {
            return false;
        }
        $isDateField = $this->isDateProperty($entity, $key);

        if ($isDateField) {
            $currentDate = $this->normalizeToDateTime($currentValue);
            $originalDate = $this->normalizeToDateTime($originalValue);
            if ($currentDate !== null && $originalDate !== null) {
                return $currentDate->getTimestamp() !== $originalDate->getTimestamp();
            }
            if (in_array($key, ['createdAt', 'updatedAt', 'created_at', 'updated_at'], true)) {
                return false;
            }
            return true;
        }

        if ($currentValue === null || $originalValue === null) {
            return true;
        }

        if (is_object($currentValue) && is_object($originalValue)) {
            if (method_exists($currentValue, 'isEqualTo')) {
                try {
                    return !$currentValue->isEqualTo($originalValue);
                } catch (Throwable) {
                }
            }
            if (method_exists($currentValue, '__toString') && method_exists($originalValue, '__toString')) {
                return (string) $currentValue !== (string) $originalValue;
            }
            return spl_object_hash($currentValue) !== spl_object_hash($originalValue);
        }

        if (is_numeric($currentValue) && is_numeric($originalValue)) {
            return (float) $currentValue !== (float) $originalValue;
        }

        return $currentValue !== $originalValue;
    }

    private function isDateProperty(Entity $entity, string $key): bool
    {
        try {
            $property = $entity->getProperty($key);
            $type = $property->getType();

            if ($type instanceof ReflectionNamedType) {
                $typeName = $type->getName();
                return $typeName === DateTimeImmutable::class
                    || $typeName === DateTime::class
                    || $typeName === DateTimeInterface::class;
            }

            // Handle union types if applicable (e.g., DateTimeImmutable|null)
            if ($type instanceof ReflectionUnionType) {
                foreach ($type->getTypes() as $namedType) {
                    $typeName = $namedType->getName();
                    if ($typeName === DateTimeImmutable::class || $typeName === DateTime::class || $typeName === DateTimeInterface::class) {
                        return true;
                    }
                }
            }
        } catch (Throwable) {
            // Fallback safety if reflection encounters issues
        }

        return false;
    }

    private function normalizeToDateTime(mixed $value): ?DateTimeInterface
    {
        if ($value instanceof DateTimeInterface) {
            return $value;
        }

        if (is_string($value) && $value !== '') {
            try {
                return new DateTimeImmutable($value);
            } catch (Throwable) {
                return null;
            }
        }

        return null;
    }
}