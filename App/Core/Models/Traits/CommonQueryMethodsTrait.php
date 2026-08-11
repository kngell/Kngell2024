<?php

declare(strict_types=1);

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

trait CommonQueryMethodsTrait
{
    protected ?string $entiKeyField = null;

    public function getById(int|string $id, ?string $field = null): ?QueryResult
    {
        if ($field !== null) {
            $payload = ModelQueryPayload::create($this->entity, [$field => $id]);
            return $this->one($payload->getConditions(), true);
        }

        $idStr = (string) $id;

        if (StringUtils::isUuidV4($idStr) || StringUtils::isUuid($idStr)) {
            $payload = ModelQueryPayload::create($this->entity, ['public_id' => $idStr]);
            return $this->one($payload->getConditions(), true);
        }

        $payload = ModelQueryPayload::create($this->entity, [$this->entity->getEntityKeyField() => $idStr]);
        $conditions = $payload->getConditions();

        if (empty($conditions) || $conditions === [$this->entity->getEntityKeyField() => $idStr]) {
            // Try as numeric ID
            if (is_numeric($idStr)) {
                return $this->find((int) $idStr);
            }
        }

        return $this->one($conditions, true);
    }

    public function countAdminList(array $conditions = []): int
    {
        $conditions = array_merge(
            $conditions,
            [ConditionListMode::MODE_ADMIN->value => true],
        );
        return $this->count($conditions);
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

    public function deleteWithOptions(array $id, string $deleteOption)
    {
        $params = [
            'conditions' => [$id['key'] => $id['value']],
            'deleteOption' => $deleteOption,
        ];
        return $this->delete($params);
    }

    public function getAllKeys(null|int|string $page = null, ?int $perPage = null, array $conditions = []): array
    {
        $ids = $this->ids($page, $perPage, $conditions);
        if ($ids->isSuccess()) {
            $this->entiKeyField = $ids->getEntityKeyField();
            return $ids->asArray();
        }
        return [];
    }

    public function hasRelationShips(): bool
    {
        return $this->entity->hasRelationships();
    }

    /**
     * @return null|string
     */
    public function getEntiKeyField(): ?string
    {
        if ($this->entiKeyField === null) {
            return $this->entity?->getEntityKeyField();
        }
        return $this->entiKeyField;
    }

    public function getAllByKeys(array $keys): array
    {
        if (empty($keys)) {
            return [];
        }
        $field = $this->entity->getEntityKeyField() ?? 'public_id';
        $conditions = [$field, $keys];

        $fetchedEntities = $this->all($conditions)->asClass();
        // dd($keys, $fetchedEntities);
        if (!is_array($fetchedEntities)) {
            return [];
        }
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
        $orderedEntities = [];
        foreach ($keys as $key) {
            if (isset($entitiesById[$key])) {
                $orderedEntities[] = $entitiesById[$key];
            }
        }

        return $orderedEntities;
    }

    protected function generateUniqueSlug(string $name): string
    {
        $baseSlug = $this->slugify($name);
        $slug = $baseSlug;
        $counter = 0;

        while ($this->one(['slug' => $slug])->exists()) {
            $counter++;
            $slug = $baseSlug . '-' . $counter;
        }

        return $slug;
    }

    protected function normalizeDataForSave(array|Entity $data): array
    {
        if ($data instanceof Entity) {
            return $data->toArray();
        }

        if (!ArrayUtils::isAssoc($data)) {
            throw new InvalidArgumentException('Save data should be an associative array.');
        }

        return $data;
    }

    protected function generatePublicId(array $data): array
    {
        $keyField = $this->entity->getEntityKeyField();
        if (empty($data[$keyField]) && empty($data['public_id'])) {
            $data['public_id'] = Uuid::uuid4()->toString();
        }
        return $data;
    }

    protected function slugify(string $text): string
    {
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        $text = preg_replace('~[^-\w]+~', '', $text);
        $text = trim($text, '-');
        $text = preg_replace('~-+~', '-', $text);
        $text = strtolower($text);

        return empty($text) ? 'n-a-' . substr(Uuid::uuid4()->toString(), 0, 8) : $text;
    }
}
