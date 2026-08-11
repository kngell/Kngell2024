<?php

declare(strict_types=1);

class QueryResultFetcher
{
    private bool $fetchModeConfigured = false;
    private bool $fetchModeAsAssociative = false;
    private ?array $cachedResults = null;

    public function __construct(
        private PDOStatement $pdoStatement,
        private QueryResultConfig $config,
        private QueryResultHydrator $hydrator,
        private EntityFactoryInterface $entityFactory,
    ) {
    }

    // public function fetchAll(): array
    // {
    //     $this->configureFetchMode();
    //     $rows = $this->pdoStatement->fetchAll();

    //     if ($this->config->getFetchMode() === 'class') {
    //         return array_map(fn ($row) => $this->hydrate($row), $rows);
    //     }

    //     return $rows ?: [];
    // }
    public function fetchAll(): array
    {
        $this->configureFetchMode();

        if ($this->cachedResults !== null) {
            return $this->cachedResults;
        }

        try {
            $results = $this->pdoStatement->fetchAll();
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'no result set') !== false) {
                throw new QueryResultException('Result set exhausted or not available');
            }

            throw $e;
        }

        return is_array($results) ? $results : [];
    }

    public function fetchFirst(): mixed
    {
        $this->configureFetchMode();
        $result = $this->pdoStatement->fetch();
        if ($result !== false) {
            $this->pdoStatement->execute();
        }

        return $result ?: null;
    }

    public function fetchSingle(): mixed
    {
        return $this->fetchFirst();
    }

    public function fetchKeyPairs(): array
    {
        // Direct fetch for key pairs
        $results = $this->pdoStatement->fetchAll(PDO::FETCH_KEY_PAIR);
        return is_array($results) ? $results : [];
    }

    public function fetchColumn(int $columnIndex = 0): array
    {
        // Direct fetch for column
        $results = $this->pdoStatement->fetchAll(PDO::FETCH_COLUMN, $columnIndex);
        return is_array($results) ? $results : [];
    }

    public function fetchPage(int $page, int $perPage): array
    {
        $offset = ($page - 1) * $perPage;
        $this->configureFetchMode();

        $allResults = $this->pdoStatement->fetchAll();
        return array_slice($allResults, $offset, $perPage);
    }

    private function configureFetchMode(): void
    {
        if ($this->fetchModeConfigured) {
            return;
        }

        $pdoFetchMode = $this->config->getPdoFetchMode();
        $className = $this->config->getClassName();
        $constructorArgs = $this->config->getConstructorArgs();

        try {
            $isClassFetch = ($pdoFetchMode & PDO::FETCH_CLASS) === PDO::FETCH_CLASS;

            if ($isClassFetch && !$this->fetchModeAsAssociative) {
                // This is a class fetch mode
                if ($className !== null) {
                    if (!empty($constructorArgs)) {
                        $this->pdoStatement->setFetchMode($pdoFetchMode, $className, $constructorArgs);
                    } else {
                        $this->pdoStatement->setFetchMode($pdoFetchMode, $className);
                    }
                } else {
                    // No class name provided, fallback to associative
                    $this->pdoStatement->setFetchMode(PDO::FETCH_ASSOC);
                }
            } elseif ($isClassFetch && $this->fetchModeAsAssociative) {
                $this->pdoStatement->setFetchMode(PDO::FETCH_ASSOC);
            } elseif ($pdoFetchMode === PDO::FETCH_COLUMN) {
                // Column fetch needs column index
                $columnIndex = $this->config->getColumnIndex() ?? 0;
                $this->pdoStatement->setFetchMode(PDO::FETCH_COLUMN, $columnIndex);
            } else {
                // All other modes (ASSOC, OBJ, KEY_PAIR) don't need extra arguments
                $this->pdoStatement->setFetchMode($pdoFetchMode);
            }

            $this->fetchModeConfigured = true;
        } catch (PDOException $exception) {
            // Fallback to associative array
            $this->pdoStatement->setFetchMode(PDO::FETCH_ASSOC);
            $this->fetchModeConfigured = true;

            // Re-throw if you want to see the error
            throw new QueryResultException(
                'Failed to set fetch mode: ' . $exception->getMessage(),
                (int) $exception->getCode(),
                $exception,
            );
        }
    }
}