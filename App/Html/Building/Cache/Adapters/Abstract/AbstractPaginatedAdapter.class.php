<?php

declare(strict_types=1);

abstract class AbstractPaginatedAdapter implements PaginatedEntityAdapterInterface
{
    protected array $filters = [];
    protected array $sort = [];
    protected string $identifierPrefix = 'c_';
    protected array $searchFields = ['name', 'slug'];

    public function __construct(
        protected Model $model,
        array $filters = [],
        array $sort = [],
    ) {
        $this->filters = $filters;
        $this->sort = $sort;
    }

    abstract public function getEntityClass(): string;

    public function getAllKeys(int $page, int $perPage): array
    {
        $conditions = $this->buildConditions();
        $conditions['limit'] = $perPage;
        $conditions['offset'] = $page;

        if (!empty($this->sort)) {
            $conditions['ORDER BY'] = $this->sort;
        }

        $results = $this->model->getAllAdminKeys($page, $perPage, $conditions);
        $keyField = $this->getKeyField();

        return array_column($results, $keyField);
    }

    public function getEntitiesByIdentifiers(array $identifiers): array
    {
        return $this->model->getAllByKeysForAdmin($identifiers);
    }

    public function getTotalCount(): int
    {
        $conditions = $this->buildConditions();
        return $this->model->countAdminList($conditions);
    }

    public function normalizeIdentifier(string $identifier): string
    {
        $prefix = $this->getIdentifierPrefix();
        if (strpos($identifier, $prefix) !== 0) {
            return $prefix . $identifier;
        }
        return $identifier;
    }

    public function getIdentifierPrefix(): string
    {
        return $this->identifierPrefix;
    }

    public function setFilters(array $filters): self
    {
        $this->filters = $filters;
        return $this;
    }

    public function setSort(array $sort): self
    {
        $this->sort = $sort;
        return $this;
    }

    protected function buildConditions(): array
    {
        $conditions = [];

        // Add active filter
        if (isset($this->filters['is_active'])) {
            $conditions['is_active'] = (bool) $this->filters['is_active'];
        }

        // Add search filter
        if (isset($this->filters['search']) && !empty($this->searchFields)) {
            $orConditions = [];
            foreach ($this->searchFields as $field) {
                $orConditions["{$field} LIKE"] = "%{$this->filters['search']}%";
            }
            $conditions['OR'] = $orConditions;
        }

        return $conditions;
    }

    protected function getKeyField(): string
    {
        $keyField = $this->model->getEntiKeyField();
        return $keyField ?: 'id';
    }

    protected function getEntitiesMethod(): string
    {
        return 'getAllByKeysForAdmin';
    }

    protected function getKeysMethod(): string
    {
        return 'getAllAdminKeys';
    }

    protected function getCountMethod(): string
    {
        return 'countAdminList';
    }

    protected function getKeyFieldValue(array $result): string
    {
        $keyField = $this->getKeyField();
        return $result[$keyField] ?? '';
    }
}