<?php

declare(strict_types=1);

class ProductStatusModel extends Model
{
    /**
     * @return ProductStatus[]
     */
    public function getActiveStatuses(): array
    {
        return $this->all(['is_active', true])->asClass();
    }
}