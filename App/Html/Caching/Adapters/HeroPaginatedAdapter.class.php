<?php

declare(strict_types=1);

final class HeroPaginatedAdapter implements PaginatedEntityAdapterInterface
{
    public function __construct(
        private HeroModel $model,
    ) {
    }

    public function getEntityClass(): string
    {
        return Hero::class;
    }

    public function getAllKeys(int $page, int $perPage): array
    {
        $results = $this->model->getAllKeys($page, $perPage);
        $keyField = $this->model->getEntiKeyField();
        if ($this->model->hasRelationShips()) {
            $keyField = $keyField ? 'h_' . $keyField : 'h_public_id';
        }

        return array_column($results, $keyField);
    }

    public function getEntitiesByIdentifiers(array $identifiers): array
    {
        return $this->model->getAllByKeys($identifiers);
    }

    public function getTotalCount(): int
    {
        return $this->model->count();
    }

    public function normalizeIdentifier(string $identifier): string
    {
        if (strpos($identifier, 'h_') !== 0) {
            return 'h_' . $identifier;
        }
        return $identifier;
    }

    public function getIdentifierPrefix(): string
    {
        return 'h_';
    }
}