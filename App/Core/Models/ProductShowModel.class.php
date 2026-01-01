<?php

declare(strict_types=1);

class ProductShowModel extends Model
{
    private null|string $entiKeyField = null;

    public function getProduct(int $productId): ?ProductShow
    {
        /** @var QueryResult $result */
        $result = $this->find($productId);
        $product = $result->asClass();
        if ($product instanceof ProductShow) {
            return $product->completeHydration();
        }
        return null;
    }

    public function getByUuid(string $productId): ?ProductShow
    {
        /** @var QueryResult $result */
        $result = $this->one(['public_id' => $productId]);
        $product = $result->asClass();

        if ($product instanceof ProductShow) {
            return $product->completeHydration();
            // dd($product, $product->debugPendingCollections(), $product->getTableAlias(), $product->getTableMap());
        }
        return null;
    }

    /**
     * @return ProductShow[]
     */
    public function getAllProducts(int $start, int $recordsPerPage): array
    {
        return $this->page($start, $recordsPerPage)->asClass();
        // $allProducts = [];
        // if ($products) {
        //     /** @var ProductShow $product */
        //     foreach ($products as $product) {
        //         $allProducts[] = $product->completeHydration();
        //     }
        // }
    }

    public function getAllProductKeys(int $page, int $perPage): array
    {
        $ids = $this->ids($page, $perPage);
        $this->entiKeyField = $ids->getEntityKeyField();
        return $ids->asArray();
    }

    /**
     * @return ProductShow[]
     */
    public function getProductsByKeys(array $keys): array
    {
        if (empty($keys)) {
            return [];
        }
        $field = $this->entity->getEntityKeyField() ?? 'public_id';
        $conditions = [$field, $keys];

        /** @var ProductShow[] $fetchedProducts */
        $fetchedProducts = $this->all($conditions)->asClass();
        // dd($keys, $fetchedProducts);
        if (!is_array($fetchedProducts)) {
            return [];
        }
        $productsById = [];
        foreach ($fetchedProducts as $product) {
            if ($product instanceof ProductShow) {
                $product->completeHydration();
                $id = $field === 'public_id' ? $product->getPublicId() : $product->getId();
                if ($id instanceof Ramsey\Uuid\UuidInterface) {
                    $productsById[$id->toString()] = $product;
                } elseif (is_string($id) || is_int($id)) {
                    $productsById[$id] = $product;
                }
            }
        }
        $orderedProducts = [];
        foreach ($keys as $key) {
            if (isset($productsById[$key])) {
                $orderedProducts[] = $productsById[$key];
            }
        }

        return $orderedProducts;
    }

    /**
     * @return null|string
     */
    public function getEntiKeyField(): ?string
    {
        return $this->entiKeyField;
    }
}