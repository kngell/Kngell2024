<?php

declare(strict_types=1);

class QueryResultFormatter
{
    private array $tableAlias = [];

    public function __construct(
        private QueryResult $queryResult,
        private QueryResultConfig $config,
        private ChangeTrackerInterface $changeTracker,
        private TypeNormalizerInterface $normalizer,
    ) {
    }

    public function asArray(): mixed
    {
        // Configure and execute once
        $this->config->setFetchMode('array');
        $this->queryResult->execute(['mode' => 'array']);

        // Get results based on operation
        $operation = $this->queryResult->getOperation();

        return $this->getResultsByOperation($operation);
    }

    public function asClass(?string $entityClass = null): mixed
    {
        $entityClass = $entityClass ?? $this->queryResult->getEntity();

        // Temporarily update config
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

            $operation = $this->queryResult->getOperation();
            return $this->getResultsByOperation($operation);
        } finally {
            $this->config->setClassName($originalClassName);
            $this->config->setFetchMode($originalMode);
        }
    }

    public function asColumn(int $columnIndex = 0): array
    {
        // Save original state
        $originalMode = $this->config->getFetchMode();
        $originalClassName = $this->config->getClassName();
        $originalConstructorArgs = $this->config->getConstructorArgs();

        try {
            // Configure for column fetch
            $this->config->setFetchMode('column');
            $this->config->setClassName(null);
            $this->config->setConstructorArgs([$columnIndex]);

            $this->queryResult->execute(['mode' => 'column']);

            // Direct column fetch
            return $this->queryResult->fetchColumn($columnIndex);
        } finally {
            // Restore original state
            $this->config->setFetchMode($originalMode);
            $this->config->setClassName($originalClassName);
            $this->config->setConstructorArgs($originalConstructorArgs);
        }
    }

    public function asKeyPairs(): array
    {
        // Save original state
        $originalMode = $this->config->getFetchMode();

        try {
            $this->config->setFetchMode('key_pair');
            $this->queryResult->execute(['mode' => 'key_pair']);

            // Direct key pairs fetch
            return $this->queryResult->fetchKeyPairs();
        } finally {
            $this->config->setFetchMode($originalMode);
        }
    }

    public function asObject(): mixed
    {
        $this->config->setFetchMode('object');
        $this->queryResult->execute(['mode' => 'object']);

        $operation = $this->queryResult->getOperation();
        return $this->getResultsByOperation($operation);
    }

    public function count(): int
    {
        // For COUNT(*) queries, use column fetch
        $columnResults = $this->asColumn(0);

        if (!empty($columnResults) && is_numeric($columnResults[0])) {
            return (int) $columnResults[0];
        }

        // Fallback to row count
        return $this->queryResult->getRowCount();
    }

    public function setTableAlias(array $tableAlias): self
    {
        $this->tableAlias = $tableAlias;
        return $this;
    }

    /**
     * Helper method to get results based on operation type.
     */
    private function getResultsByOperation(string $operation): mixed
    {
        return match ($operation) {
            'first', 'single' => $this->queryResult->first(),
            'last' => $this->queryResult->last(),
            default => $this->queryResult->all()
        };
    }
}