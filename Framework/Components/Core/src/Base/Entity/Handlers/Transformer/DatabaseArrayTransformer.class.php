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

        if ($this->isEntityCollection($value)) {
            return $this->transformCollection($value);
        }

        return $value;
    }

    private function isEntityCollection(mixed $value): bool
    {
        if (is_array($value) && !empty($value)) {
            $firstItem = reset($value);
            return $firstItem instanceof Entity;
        }

        return false;
    }

    private function transformCollection(mixed $collection): array
    {
        $result = [];

        foreach ($collection as $index => $item) {
            if ($item instanceof Entity) {
                $result[$index] = $this->transformRecursive($item);
            } else {
                $result[$index] = $item;
            }
        }

        return $result;
    }
}