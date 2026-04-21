<?php

declare(strict_types=1);

class CategoryModel extends AbstractSaveModel
{
    /**
     * @throws PDOException
     * @throws QueryResultException
     *
     * @return Category[]
     */
    public function getActiveCategories(): array
    {
        return $this->all(['is_active' => true])->asClass();
    }

    protected function validateData(array $data): void
    {
    }

    protected function generateMissingFields(array $data): array
    {
        $data = $this->generatePublicId($data);
        if (empty($data['slug']) && $this->entity->hasProperty('slug')) {
            $data['slug'] = $this->generateUniqueSlug($data['name']);
        }
        return $data;
    }
}