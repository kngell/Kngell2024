<?php

declare(strict_types=1);

class SimpleArrayTransformer implements ToArrayTransformerInterface
{
    private WeakMap $transformationCache;
    private array $processingStack = [];

    public function __construct(
        private TypeNormalizerInterface $normalizer,
    ) {
        $this->transformationCache = new WeakMap();
    }

    public function supports(string $format): bool
    {
        return $format === 'simple';
    }

    public function transform(Entity $entity, array $options = []): array
    {
        if ($this->transformationCache->offsetExists($entity) && !$entity->isTracking()) {
            return $this->transformationCache[$entity];
        }

        $entityId = spl_object_id($entity);

        if (in_array($entityId, $this->processingStack, true)) {
            return ['__reference__' => $entity->getFieldValue($entity->getEntityKeyField())];
        }

        $this->processingStack[] = $entityId;

        $array = $this->doTransform($entity, $options);

        $this->transformationCache[$entity] = $array;

        array_pop($this->processingStack);

        return $array;
    }

    public function clearCache(): void
    {
        $this->transformationCache = new WeakMap();
        $this->processingStack = [];
    }

    public function getCacheStats(): array
    {
        $count = 0;
        foreach ($this->transformationCache as $key => $value) {
            $count++;
        }

        return [
            'cached_entities' => $count,
            'current_stack_depth' => count($this->processingStack),
            'memory_usage_mb' => round(memory_get_usage() / 1024 / 1024, 2),
        ];
    }

    private function doTransform(Entity $entity, array $options = []): array
    {
        $array = [];
        $reflection = CustomReflection::getInstance($entity)->getClass();

        foreach ($reflection->getProperties(ReflectionProperty::IS_PRIVATE) as $property) {
            $propertyName = $property->getName();

            if (!$property->isInitialized($entity)) {
                continue;
            }

            if ($propertyName === 'tableAlias' || $propertyName === 'tableMap') {
                continue;
            }

            $dbFieldName = StringUtils::camelCaseToSnakeCase($propertyName);
            $value = $property->getValue($entity);

            if ($value instanceof EntityDependenciesFactory) {
                continue;
            }

            if ($value instanceof Entity) {
                $array[$dbFieldName] = $value;
                continue;
            }

            if (is_array($value) && $this->isEntityArray($value)) {
                $array[$dbFieldName] = $value;
                continue;
            }

            $array[$dbFieldName] = $this->normalizer->normalizeFromEntityToDatabase($value, $property);
        }

        return $array;
    }

    private function isEntityArray(array $value): bool
    {
        if (empty($value)) {
            return false;
        }

        $firstItem = reset($value);
        return $firstItem instanceof Entity;
    }
}