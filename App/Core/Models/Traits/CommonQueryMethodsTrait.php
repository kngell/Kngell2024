<?php

declare(strict_types=1);

use Ramsey\Uuid\Uuid;

trait CommonQueryMethodsTrait
{
    public function getById(int|string $id, ?string $field = null): ?object
    {
        if ($field !== null) {
            return $this->one([$field => $id], true)?->asClass();
        }
        return null;
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