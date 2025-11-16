<?php

declare(strict_types=1);

class CategoryModel extends Model
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
}