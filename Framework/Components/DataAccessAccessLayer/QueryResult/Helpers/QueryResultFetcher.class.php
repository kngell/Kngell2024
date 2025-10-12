<?php

declare(strict_types=1);

class QueryResultFetcher
{
    public function __construct(
        private PDOStatement $pdoStatement,
        private QueryResultConfig $config,
    ) {
    }

    /**
     * Get results with optional limit.
     */
    public function get(?int $limit = null): array
    {
        $results = $this->fetchAll();
        return $limit !== null ? array_slice($results, 0, $limit) : $results;
    }

    /**
     * Get all records from result set.
     */
    public function fetchAll(): array
    {
        $this->configureFetchMode();
        $results = $this->pdoStatement->fetchAll();
        return is_array($results) ? $results : [];
    }

    /**
     * Get first record from result set.
     */
    public function fetchFirst(): mixed
    {
        $this->configureFetchMode();
        $result = $this->pdoStatement->fetch();
        return $result ?: null;
    }

    /**
     * Get a single record (alias for fetchFirst).
     */
    public function fetchSingle(): mixed
    {
        return $this->fetchFirst();
    }

    /**
     * Get results as key-value pairs.
     */
    public function fetchKeyPairs(): array
    {
        $results = $this->pdoStatement->fetchAll(PDO::FETCH_KEY_PAIR);
        return is_array($results) ? $results : [];
    }

    /**
     * Get a column as an array.
     */
    public function fetchColumn(int $columnIndex = 0): array
    {
        $results = $this->pdoStatement->fetchAll(PDO::FETCH_COLUMN, $columnIndex);
        return is_array($results) ? $results : [];
    }

    /**
     * Get paginated results.
     */
    public function fetchPage(int $page, int $perPage): array
    {
        $offset = ($page - 1) * $perPage;

        // Handle special fetch modes
        if ($this->config->getFetchMode() === 'key_pair') {
            $results = $this->pdoStatement->fetchAll(PDO::FETCH_KEY_PAIR);
            return array_slice($results, $offset, $perPage);
        }

        if ($this->config->getFetchMode() === 'column') {
            $columnIndex = $this->config->getConstructorArgs()[0] ?? 0;
            $results = $this->pdoStatement->fetchAll(PDO::FETCH_COLUMN, $columnIndex);
            return array_slice($results, $offset, $perPage);
        }

        // For class/array/object modes
        $this->configureFetchMode();
        $results = $this->pdoStatement->fetchAll();
        return array_slice($results, $offset, $perPage);
    }

    /**
     * Apply operation to results (first, limit, etc.).
     */
    public function applyOperation(array $results, string $operation, ?int $limit = null): mixed
    {
        $results = $results ?: [];

        return match ($operation) {
            'all' => $results,
            'first' => $results[0] ?? null,
            'limit' => array_slice($results, 0, $limit),
            default => $results
        };
    }

    /**
     * Internal method to handle different fetch operations.
     */
    public function fetchResults(string $operation, ?int $limit = null): mixed
    {
        // Handle special fetch modes
        if ($this->config->getFetchMode() === 'key_pair') {
            $results = $this->pdoStatement->fetchAll(PDO::FETCH_KEY_PAIR);
            return $this->applyOperation($results, $operation, $limit);
        }

        if ($this->config->getFetchMode() === 'column') {
            $columnIndex = $this->config->getConstructorArgs()[0] ?? 0;
            $results = $this->pdoStatement->fetchAll(PDO::FETCH_COLUMN, $columnIndex);
            return $this->applyOperation($results, $operation, $limit);
        }

        // For class/array/object modes
        $this->configureFetchMode();
        $results = $this->pdoStatement->fetchAll();
        return $this->applyOperation($results, $operation, $limit);
    }

    /**
     * Configure the PDO fetch mode.
     */
    private function configureFetchMode(): void
    {
        $pdoFetchMode = $this->config->getPdoFetchMode();
        $className = $this->config->getClassName();
        $constructorArgs = $this->config->getConstructorArgs();

        try {
            if ($className !== null) {
                if ($constructorArgs !== null) {
                    $this->pdoStatement->setFetchMode($pdoFetchMode, $className, $constructorArgs);
                } else {
                    $this->pdoStatement->setFetchMode($pdoFetchMode, $className);
                }
            } else {
                $this->pdoStatement->setFetchMode($pdoFetchMode);
            }
        } catch (PDOException $exception) {
            $this->pdoStatement->setFetchMode(PDO::FETCH_ASSOC);
            throw new QueryResultException(
                'Failed to set fetch mode: ' . $exception->getMessage(),
                (int) $exception->getCode(),
                $exception,
            );
        }
    }
}