<?php

declare(strict_types=1);

use Ramsey\Uuid\UuidInterface;

class HeroModel extends AbstractSaveModel
{
    private const string IMG_URL = 'image_url';

    public function save(null|array|Entity $data = null, array $conditions = []): QueryResult
    {
        return parent::save($data, $conditions);
    }

    public function countAdminList(array $conditions = []): int
    {
        $conditions = array_merge(
            $conditions,
            [
                ConditionListMode::MODE_ADMIN->value => true,
            ],
        );
        return parent::count($conditions);
    }

    public function getAllKeys(int $page, int $perPage): array
    {
        $ids = $this->ids($page, $perPage);
        if ($ids->isSuccess()) {
            $this->entiKeyField = $ids->getEntityKeyField();
            return $ids->asArray();
        }
        return [];
    }

    public function getAllAdminKeys(int $page, int $perPage, array $extraConditions = []): array
    {
        $conditions = array_merge(
            $extraConditions,
            [
                ConditionListMode::MODE_ADMIN->value => true,
            ],
        );

        $result = $this->ids($page, $perPage, $conditions);

        if (!$result->isSuccess() || $result->isEmpty()) {
            return [];
        }

        // Store entity key field if needed elsewhere
        $this->entiKeyField = $result->getEntityKeyField();

        return $result->asArray();
    }

    public function getAllByKeysForAdmin(array $keys): array
    {
        if (empty($keys)) {
            return [];
        }

        $field = $this->entity->getEntityKeyField() ?? 'public_id';

        // Apply admin mode to ensure non-deleted only
        $conditions = [
            ConditionListMode::MODE_ADMIN->value => true,
            $field => $keys,
        ];

        $fetchedEntities = $this->all($conditions)->asClass();

        if (!is_array($fetchedEntities)) {
            return [];
        }

        // Re-index by ID for ordering
        $entitiesById = [];
        foreach ($fetchedEntities as $entity) {
            $entity->completeHydration();
            $id = $field === 'public_id' ? $entity->getPublicId() : $entity->getEntityPrimaryKeyValue();

            if ($id instanceof UuidInterface) {
                $entitiesById[$id->toString()] = $entity;
            } elseif (is_string($id) || is_int($id)) {
                $entitiesById[$id] = $entity;
            }
        }

        // Return in original key order
        $orderedEntities = [];
        foreach ($keys as $key) {
            $keyValue = $key instanceof UuidInterface ? $key->toString() : $key;
            if (isset($entitiesById[$keyValue])) {
                $orderedEntities[] = $entitiesById[$keyValue];
            }
        }

        return $orderedEntities;
    }

    public function getAdminList(?string $pageTarget = null): array
    {
        $conditions = [];

        if ($pageTarget) {
            $conditions['page_target'] = $pageTarget;
        }
        // Add admin mode flag
        $conditions[ConditionListMode::MODE_ADMIN->value] = true;
        $conditions = array_merge($conditions, [
            'ORDER_BY' => ['sort_order ASC', 'created_at DESC'],
        ]);

        return $this->all(['conditions' => $conditions])->asClass();
    }

    public function getHero(int $heroId): ?Hero
    {
        return $this->one(['hero_id' => $heroId])?->asClass();
    }

    public function getImagesPath(): array
    {
        $params['columns'] = ['image_url'];
        return $this->all($params)->asArray();
    }

    protected function generateMissingFields(array $data): array
    {
        return $this->generatePublicId($data);
    }

    protected function validateData(array $data): void
    {
    }
}