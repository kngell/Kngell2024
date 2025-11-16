<?php

declare(strict_types=1);

class ChangeTracker implements ChangeTrackerInterface
{
    /** * @var array<string, array<string, mixed>> Stores original entity data: [EntityHash => [propertyName => originalValue]]
     */
    private array $originalData = [];

    public function track(object $entity): void
    {
        $hash = $this->getEntityHash($entity);

        if (isset($this->originalData[$hash])) {
            return;
        }

        $reflection = new ReflectionClass($entity);
        $data = [];

        foreach ($reflection->getProperties() as $property) {
            $property->setAccessible(true);
            $data[$property->getName()] = $property->getValue($entity);
        }

        $this->originalData[$hash] = $data;
    }

    public function getChanges(object $entity): array
    {
        $hash = $this->getEntityHash($entity);

        if (!isset($this->originalData[$hash])) {
            return [];
        }

        $original = $this->originalData[$hash];
        $currentChanges = [];
        $reflection = new ReflectionClass($entity);

        foreach ($reflection->getProperties() as $property) {
            $propertyName = $property->getName();

            if (!isset($original[$propertyName])) {
                continue;
            }

            $property->setAccessible(true);
            $currentValue = $property->getValue($entity);
            $originalValue = $original[$propertyName];

            if ($this->hasValueChanged($currentValue, $originalValue)) {
                $currentChanges[$propertyName] = $currentValue;
            }
        }

        return $currentChanges;
    }

    public function clear(object $entity): void
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

    private function hasValueChanged(mixed $currentValue, mixed $originalValue): bool
    {
        if ($currentValue === $originalValue) {
            return false;
        }
        if (is_object($currentValue) && is_object($originalValue)) {
            if ($currentValue instanceof Brick\Money\Money && $originalValue instanceof Brick\Money\Money) {
                return !$currentValue->isEqualTo($originalValue);
            }

            return $currentValue !== $originalValue;
        }
        return true;
    }
}