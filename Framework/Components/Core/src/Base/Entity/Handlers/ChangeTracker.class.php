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
            // if ($key === 'short_description') {
            //     // If this shows up, the loop is working.
            //     // If it doesn't show up, $current doesn't have the key 'short_description'
            //     // even if your dd() says it does (check for hidden spaces in keys!)
            //     dump("Key: $key", "Orig: $originalValue", "Curr: $currentValue");
            // }

            if ($this->hasValueChanged($currentValue, $originalValue)) {
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

    private function getEntityHash(object $entity): string
    {
        $id = method_exists($entity, 'getId') ? $entity->getId() : null;

        if ($id === null) {
            return spl_object_hash($entity);
        }

        return get_class($entity) . ':' . $id;
    }

    // private function hasValueChanged(mixed $currentValue, mixed $originalValue): bool
    // {
    //     if ($currentValue === $originalValue) {
    //         return false;
    //     }
    //     if (is_object($currentValue) && is_object($originalValue)) {
    //         if ($currentValue instanceof Brick\Money\Money && $originalValue instanceof Brick\Money\Money) {
    //             return !$currentValue->isEqualTo($originalValue);
    //         }

    //         return $currentValue !== $originalValue;
    //     }
    //     return true;
    // }

    private function hasValueChanged(mixed $currentValue, mixed $originalValue): bool
    {
        // 1. Same value/type = No change
        if ($currentValue === $originalValue) {
            return false;
        }

        // // 2. Normalize NULL vs Empty Strings (Common HTML Form issue)
        // // This prevents [description] => "" from appearing in dirty data if it was NULL
        // $isActuallyEmptyOriginal = ($originalValue === null || $originalValue === '');
        // $isActuallyEmptyCurrent = ($currentValue === null || $currentValue === '');

        // if ($isActuallyEmptyOriginal && $isActuallyEmptyCurrent) {
        //     return false;
        // }

        // // 3. Handle Recursive Arrays (Nested Entities)
        // // If you are comparing two arrays (like a transformed Category entity)
        // if (is_array($currentValue) && is_array($originalValue)) {
        //     // If counts differ, it's definitely changed
        //     if (count($currentValue) !== count($originalValue)) {
        //         return true;
        //     }
        //     // Recursively check if any internal value changed
        //     foreach ($currentValue as $key => $val) {
        //         if (!array_key_exists($key, $originalValue) || $this->hasValueChanged($val, $originalValue[$key])) {
        //             return true;
        //         }
        //     }
        //     return false;
        // }

        // // 4. Scalar comparison (String/Int/Float)
        // if (is_scalar($currentValue) && is_scalar($originalValue)) {
        //     return (string) $currentValue !== (string) $originalValue;
        // }

        return true;
    }
}