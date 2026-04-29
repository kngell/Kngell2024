<?php

declare(strict_types=1);

use Ramsey\Uuid\Uuid;

trait CommonQueryMethodsTrait
{
    protected ?string $entiKeyField = null;

    public function getById(int|string $id, ?string $field = null): ?object
    {
        if ($id && ctype_digit($id)) {
            return $this->find($id)?->asClass();
        }
        if ($this->isUuidV4($id)) {
            return $this->one(['public_id' => $id], true)?->asClass();
        }
        if ($field !== null) {
            return $this->one([$field => $id], true)?->asClass();
        }
        return null;
    }

    public function getAllKeys(int $page, int $perPage): array
    {
        $ids = $this->ids($page, $perPage, ['deleted_at IS NULL']);
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
        $conditions = [$field, $keys, 'deleted_at IS NULL'];

        $fetchedEntities = $this->all($conditions)->asClass();
        // dd($keys, $fetchedEntities);
        if (!is_array($fetchedEntities)) {
            return [];
        }
        $entitiesById = [];
        foreach ($fetchedEntities as $entity) {
            $entity->completeHydration();
            $id = $field === 'public_id' ? $entity->getPublicId() : $entity->getEntityPrimaryKeyValue();
            if ($id instanceof Ramsey\Uuid\UuidInterface) {
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

    private function isUuidV4(string $str): bool
    {
        return preg_match(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
            $str,
        ) === 1;
    }
}