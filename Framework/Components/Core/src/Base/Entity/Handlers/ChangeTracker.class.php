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
            if ($this->hasValueChanged($currentValue, $originalValue, $entity)) {
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

    // private function getEntityHash(Entity $entity): string
    // {
    //     $id = $entity->entityKeyIsInitialzed() ? $entity->getEntityKeyProperty() : null;

    //     if ($id === null) {
    //         return spl_object_hash($entity);
    //     }

    //     return get_class($entity) . ':' . $id;
    // }

    private function hasValueChanged(mixed $currentValue, mixed $originalValue, Entity $entity): bool
    {
        //-----
        if ($currentValue === $originalValue) {
            return false;
        }

        if ($currentValue === null || $originalValue === null) {
            return true;
        }

        if ($currentValue === $originalValue) {
            return false;
        }

        if ($currentValue === null || $originalValue === null) {
            return true;
        }

        // Handle isEqualTo method when both are objects
        if (is_object($currentValue) && method_exists($currentValue, 'isEqualTo') && is_object($originalValue)) {
            try {
                return !$currentValue->isEqualTo($originalValue);
            } catch (Throwable $e) {
                // Fallback if types are incompatible for isEqualTo
            }
        }

        // Handle objects (both must be objects for comparison)
        if (is_object($currentValue) && is_object($originalValue)) {
            // Try isEqualTo if it exists but wasn't caught above
            if (method_exists($currentValue, 'isEqualTo')) {
                try {
                    return !$currentValue->isEqualTo($originalValue);
                } catch (Throwable $e) {
                    // Fall through to string comparison
                }
            }
            return (string) $currentValue !== (string) $originalValue;
        }

        // Handle case where one is object and the other isn't
        if (is_object($currentValue) || is_object($originalValue)) {
            return true; // Different types (object vs non-object) means changed
        }

        // Default strict comparison for primitives
        return $currentValue !== $originalValue;
    }
    // private function hasValueChanged(mixed $currentValue, mixed $originalValue): bool
    // {
    //     if ($currentValue === $originalValue) {
    //         return false;
    //     }

    //     if (method_exists($currentValue, 'isEqualTo') && is_object($originalValue)) {
    //         try {
    //             return !$currentValue->isEqualTo($originalValue);
    //         } catch (Throwable $e) {
    //             // Fallback if types are incompatible for isEqualTo
    //         }
    //     }

    //     if (is_object($currentValue) || is_object($originalValue)) {
    //         return (string) $currentValue !== (string) $originalValue;
    //     }

    //     return $currentValue !== $originalValue;
    // }
}
