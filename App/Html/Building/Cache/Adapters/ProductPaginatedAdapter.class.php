<?php

declare(strict_types=1);

final class ProductPaginatedAdapter extends AbstractPaginatedAdapter
{
    protected string $identifierPrefix = 'p_';
    protected array $searchFields = ['name', 'description', 'sku'];

    public function __construct(
        ProductShowModel $productModel,
        array $filters = [],
        array $sort = ['name' => 'ASC'],
    ) {
        parent::__construct($productModel, $filters, $sort);
    }

    public function getEntityClass(): string
    {
        return ProductShow::class;
    }

    public function getAllKeys(int $page, int $perPage): array
    {
        // Products use a different method signature
        $results = $this->model->getAllProductKeys($page, $perPage);
        $keyField = $this->getKeyField();
        $keyField = $keyField ? 'p_' . $keyField : 'p_public_id';

        return array_column($results, $keyField);
    }

    public function getEntitiesByIdentifiers(array $identifiers): array
    {
        return $this->model->getProductsByKeys($identifiers);
    }

    public function getTotalCount(): int
    {
        return $this->model->count();
    }

    protected function getKeyField(): string
    {
        $keyField = parent::getKeyField();
        return $keyField ?: 'public_id';
    }

    protected function buildConditions(): array
    {
        $conditions = parent::buildConditions();

        // Add product-specific conditions if needed
        if (isset($this->filters['category_id'])) {
            $conditions['category_id'] = (int) $this->filters['category_id'];
        }

        if (isset($this->filters['price_min'])) {
            $conditions['price >= '] = (float) $this->filters['price_min'];
        }

        if (isset($this->filters['price_max'])) {
            $conditions['price <= '] = (float) $this->filters['price_max'];
        }

        return $conditions;
    }
}