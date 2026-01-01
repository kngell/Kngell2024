<?php

declare(strict_types=1);

class DatabaseArrayTransformer implements ToArrayTransformerInterface
{
    private array $processedEntities = [];

    public function __construct(
        private SimpleArrayTransformer $simpleArrayStrategy,
    ) {
    }

    public function supports(string $format): bool
    {
        return $format === 'database';
    }

    public function transform(Entity $entity, array $options = []): array
    {
        $this->processedEntities = [];
        return $this->transformRecursive($entity);
    }

    private function transformRecursive(Entity $entity): array
    {
        $entityId = spl_object_id($entity);
        if (in_array($entityId, $this->processedEntities, true)) {
            return ['id' => $entity->getFieldValue($entity->getEntityKeyField())];
        }
        $this->processedEntities[] = $entityId;

        $array = $this->simpleArrayStrategy->transform($entity);

        foreach ($array as &$value) {
            $value = $this->transformValueRecursive($value);
        }

        return $array;
    }

    private function transformValueRecursive(mixed $value): mixed
    {
        if ($value instanceof Entity) {
            return $this->transformRecursive($value);
        }

        // Handle collections/iterables
        if ($this->isEntityCollection($value)) {
            return $this->transformCollection($value);
        }

        // Already normalized by SimpleArrayTransformer
        return $value;
    }

    private function isEntityCollection(mixed $value): bool
    {
        // Current: arrays containing objects
        if (is_array($value) && ArrayUtils::isObjectList($value)) {
            return true;
        }

        // Future: CollectionInterface objects
        if ($value instanceof CollectionInterface) {
            return true;
        }

        // Future: Any iterable containing entities
        if (is_iterable($value) && !is_array($value)) {
            // Check first item to see if it contains entities
            foreach ($value as $item) {
                return $item instanceof Entity;
            }
        }

        return false;
    }

    private function transformCollection(mixed $collection): array
    {
        $result = [];

        // Convert to array if it's a non-array iterable
        if (is_iterable($collection) && !is_array($collection)) {
            $collection = iterator_to_array($collection);
        }

        if (is_array($collection)) {
            foreach ($collection as $index => $item) {
                if ($item instanceof Entity) {
                    $result[$index] = $this->transformRecursive($item);
                } else {
                    $result[$index] = $item;
                }
            }
        }

        return $result;
    }
}