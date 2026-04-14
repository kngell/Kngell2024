<?php

declare(strict_types=1);

class QueryResultFormatter
{
    public function __construct(
        private QueryResult $queryResult,
        private QueryResultConfig $config,
        private EntityFactoryInterface $entityFactory,
        private CartesianHydrator $cartesianHydrator,
        private CartesianDetector $cartesianDetector,
        private string $entityClass,
    ) {
    }

    public function asArray(): mixed
    {
        $this->config->setFetchMode('array');
        $this->queryResult->execute(['mode' => 'array']);

        return $this->getResultsByOperation();
    }

    public function asClass(?string $entityClass = null): mixed
    {
        $entityClass = $entityClass ?? $this->entityClass;
        $hasRelationships = $this->entityFactory->hasRelationships($entityClass);

        if ($this->requiresCartesianHandling($entityClass) || $hasRelationships) {
            return $this->asClassWithRelations($entityClass);
        }

        return $this->asClassStandard($entityClass);
    }

    public function asClassWithRelations(?string $entityClass = null): mixed
    {
        $entityClass = $entityClass ?? $this->queryResult->getEntity();

        $originalOperation = $this->queryResult->getOperation();
        if ($originalOperation === 'single') {
            $this->queryResult->setOperation('all');
        }

        try {
            $rows = $this->queryResult->asArray();

            if (empty($rows)) {
                return $this->handleEmptyResult();
            }

            if ($originalOperation === 'all') {
                return $this->cartesianHydrator->hydrateCollection(
                    $rows,
                    $entityClass,
                    $this->config->getTableAlias(),
                    $this->config->getTableMap(),
                );
            }
            return $this->cartesianHydrator->hydrateSingle(
                $rows,
                $entityClass,
                $this->config->getTableAlias(),
                $this->config->getTableMap(),
            );
        } finally {
            if ($originalOperation === 'single') {
                $this->queryResult->setOperation($originalOperation);
            }
        }
    }

    public function asColumn(int $columnIndex = 0): array
    {
        $originalMode = $this->config->getFetchMode();
        $originalClassName = $this->config->getClassName();

        try {
            $this->config->setFetchMode('column');
            $this->config->setClassName(null);

            $this->queryResult->execute(['mode' => 'column']);

            return $this->queryResult->fetchColumn($columnIndex);
        } finally {
            $this->config->setFetchMode($originalMode);
            $this->config->setClassName($originalClassName);
        }
    }

    public function asKeyPairs(): array
    {
        $originalMode = $this->config->getFetchMode();

        try {
            $this->config->setFetchMode('key_pair');
            $this->queryResult->execute(['mode' => 'key_pair']);

            return $this->queryResult->fetchKeyPairs();
        } finally {
            $this->config->setFetchMode($originalMode);
        }
    }

    public function asObject(): mixed
    {
        $this->config->setFetchMode('object');
        $this->queryResult->execute(['mode' => 'object']);

        return $this->getResultsByOperation();
    }

    public function count(): int
    {
        $columnResults = $this->asColumn(0);

        if (!empty($columnResults) && is_numeric($columnResults[0])) {
            return (int) $columnResults[0];
        }

        return $this->queryResult->getRowCount();
    }

    private function requiresCartesianHandling(string $entityClass): bool
    {
        $primaryKeyField = $this->entityFactory->getPrimaryKeyField($entityClass);
        $operation = $this->queryResult->getOperation();
        $queryString = $this->queryResult->getQueryString();

        // Check for single entity with joins
        if ($this->cartesianDetector->isSingleEntityWithJoins($queryString, $operation, $primaryKeyField)) {
            return true;
        }

        // Check for collection that needs cartesian handling
        if ($this->cartesianDetector->collectionRequiresCartesianHandling($queryString, $operation, $primaryKeyField)) {
            return true;
        }

        return false;
    }

    private function asClassStandard(?string $entityClass = null): mixed
    {
        $entityClass = $entityClass ?? $this->queryResult->getEntity();

        $originalClassName = $this->config->getClassName();
        $originalMode = $this->config->getFetchMode();

        try {
            $this->config->setFetchMode('class');
            $this->config->setClassName($entityClass);

            $this->queryResult->execute([
                'mode' => 'class',
                'class' => $entityClass,
                'constructor_args' => $this->config->getConstructorArgs(),
            ]);

            return $this->getResultsByOperation();
        } finally {
            $this->config->setClassName($originalClassName);
            $this->config->setFetchMode($originalMode);
        }
    }

    private function handleEmptyResult(): mixed
    {
        $operation = $this->queryResult->getOperation();
        $fetchStrategy = $this->queryResult->getFetchStrategy();
        if ($fetchStrategy === FetchStrategy::RELATIONSHIP_AWARE) {
            return null;
        }
        return match ($operation) {
            'all' => [],
            'first', 'single', 'last' => null,
            default => null
        };
    }

    private function getResultsByOperation(): mixed
    {
        $operation = $this->queryResult->getOperation();

        return match ($operation) {
            'first', 'single' => $this->queryResult->first(),
            'last' => $this->queryResult->last(),
            default => $this->queryResult->all()
        };
    }
}