<?php

declare(strict_types=1);

class ProductShowModel extends Model
{
    public function deleteProduct(int $id): bool
    {
        $queryResult = $this->find($id);
        if ($queryResult->exists()) {
            /** @var ProductShow $entity */
            $entity = $queryResult->asClass();
            $entity->completeHydration()->track();
            $result = $this->delete($entity);
            return $result->isSuccess();
        }
        return false;
    }

    public function getProduct(int $productId): ?ProductShow
    {
        /* @var QueryResult $result */
        return $this->one(['pdt_id' => $productId], true)?->asClass();
    }

    public function getByUuid(string $productId): ?ProductShow
    {
        /* @var QueryResult $result */
        return $this->one(['public_id' => $productId], true)?->asClass();
    }

    /**
     * @return ProductShow[]
     */
    public function getAllProducts(int $start, int $recordsPerPage): array
    {
        return $this->page($start, $recordsPerPage)->asClass();
    }

    public function getAllProductKeys(int $page, int $perPage): array
    {
        $ids = $this->ids($page, $perPage, ['deleted_at IS NULL']);
        $this->entiKeyField = $ids->getEntityKeyField();
        return $ids->asArray();
    }

    public function count(array $conditions = []): int
    {
        $conditions = array_merge($conditions, ['deleted_at' => 'IS NULL']);
        return parent::count($conditions);
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
        $conditions = [$field, $keys, 'deleted_at', 'IS NULL'];

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
}