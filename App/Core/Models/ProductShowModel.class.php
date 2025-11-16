<?php

declare(strict_types=1);
class ProductShowModel extends Model
{
    public function getProduct(int $productId): ?ProductShow
    {
        return $this->find($productId)->asClass();
    }
}