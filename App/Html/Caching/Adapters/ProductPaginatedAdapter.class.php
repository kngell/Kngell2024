<?php

declare(strict_types=1);

final class ProductPaginatedAdapter implements PaginatedEntityAdapterInterface
{
    public function __construct(
        private ProductShowModel $productModel,
    ) {
    }

    public function getEntityClass(): string
    {
        return ProductShow::class;
    }

    public function getAllKeys(int $page, int $perPage): array
    {
        $results = $this->productModel->getAllProductKeys($page, $perPage);
        $keyField = $this->productModel->getEntiKeyField();
        $keyField = $keyField ? 'p_' . $keyField : 'p_public_id';
        return array_column($results, $keyField);
    }

    public function getEntitiesByIdentifiers(array $identifiers): array
    {
        return $this->productModel->getProductsByKeys($identifiers);
    }

    public function getTotalCount(): int
    {
        return $this->productModel->count();
    }

    public function normalizeIdentifier(string $identifier): string
    {
        if (strpos($identifier, 'p_') !== 0) {
            return 'p_' . $identifier;
        }
        return $identifier;
    }

    public function getIdentifierPrefix(): string
    {
        return 'p_';
    }
}