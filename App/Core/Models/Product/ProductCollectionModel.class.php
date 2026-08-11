<?php

declare(strict_types=1);

class ProductCollectionModel extends Model
{
    public function getProduct(int $productId): ?ProductCollection
    {
        $product = $this->getById($productId, 'pdt_id');
        if ($product->exists()) {
            return $product->asClass();
        }
        return null;
    }

    public function getProductsByIds(array $productIds): array
    {
        $result = $this->all(['pdt_id' => $productIds]);
        if ($result->isSuccess()) {
            return $result->asClass();
        }
        return [];
    }
}