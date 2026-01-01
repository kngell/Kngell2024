<?php

declare(strict_types=1);

final class EntityCacheDataSerializer implements EntityDataSerializerInterface
{
    public function __construct(
        private EntityFactory $entityFactory,
        private EntityToArrayTransformer $transformer,
    ) {
    }

    public function getData(Entity $entity): array
    {
        $data = $this->transformer->toDatabaseArray($entity, true);
        return [
            '__main_data' => $data,
            '__entity_meta' => [
                'class' => get_class($entity),
                'timestamp' => time(),
                'format' => 'database_array',
                'version' => '1.0',
            ],
        ];
    }

    public function restoreData(array $data): ?Entity
    {
        $meta = $data['__entity_meta'] ?? [];
        $entityClass = $meta['class'] ?? null;

        if (!$entityClass || !class_exists($entityClass) || !is_subclass_of($entityClass, Entity::class)) {
            return null;
        }

        try {
            $mainData = $data['__main_data'] ?? [];
            return $this->entityFactory->createFromClient($entityClass, $mainData);
        } catch (Throwable $e) {
            throw new CacheException('Failed to restore entity from cache data: ' . $e->getMessage());
        }
    }
}