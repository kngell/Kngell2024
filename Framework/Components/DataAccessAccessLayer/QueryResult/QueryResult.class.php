<?php

declare(strict_types=1);

class QueryResult implements Countable, IteratorAggregate
{
    private QueryResultConfig $config;
    private QueryResultFetcher $fetcher;
    private QueryResultFormatter $formatter;
    private bool $isExecuted = false;
    private string $operation = 'all';
    private QueryResultPaginator $paginator;

    /** @var PDOStatement The PDO statement object */
    private PDOStatement $pdoStatement;

    private int $rowCount = 0;

    public function __construct(
        private DataMapperInterface $dataMapper,
        private Entity $entity,
    ) {
        $this->initializeComponents();
        $this->initializeQueryStatement();
    }

    /**
     * Destructor to ensure resources are freed.
     */
    public function __destruct()
    {
        $this->close();
    }

    /**
     * Magic method for backward compatibility with old method names.
     *
     * @deprecated Use explicit methods instead
     */
    public function __call(string $method, array $arguments): mixed
    {
        $methodMap = [
            'single' => 'getSingle',
            'first' => 'getFirst',
            'all' => 'getAll',
            'rowCount' => 'getRowCount',
        ];

        if (isset($methodMap[$method])) {
            trigger_error(
                "Method {$method}() is deprecated. Use {$methodMap[$method]}() instead.",
                E_USER_DEPRECATED,
            );
            return $this->{$methodMap[$method]}(...$arguments);
        }

        throw new BadMethodCallException("Method {$method} does not exist in " . static::class);
    }

    public function all(): array
    {
        $this->operation = 'all';
        $this->ensureExecuted();

        $results = $this->fetcher->fetchAll();
        return $this->paginator->applyPagination($results, 'all');
    }

    public function last(): mixed
    {
        $this->operation = 'last';
        $this->ensureExecuted();

        if ($this->paginator->getLastLimit() !== null && $this->paginator->getLastLimit() > 1) {
            $results = $this->fetcher->fetchAll();
            $limited = array_slice($results, -$this->paginator->getLastLimit());
            return $limited[0] ?? null;
        }

        $results = $this->fetcher->fetchAll();
        return !empty($results) ? end($results) : null;
    }

    public function single(): mixed
    {
        $this->operation = 'single';
        return $this->fetcher->fetchSingle();
    }

    public function execute(string|array|null $fetchOptions = null): self
    {
        if ($this->isExecuted) {
            throw new QueryResultException('Query has already been executed');
        }

        try {
            $this->config->processFetchOptions($fetchOptions, $this->entity);
            $this->rowCount = $this->pdoStatement->rowCount();
            $this->isExecuted = true;
            return $this;
        } catch (PDOException $exception) {
            throw new QueryResultException(
                'Failed to execute query: ' . $exception->getMessage(),
                (int) $exception->getCode(),
                $exception,
            );
        }
    }

    public function asArray(): mixed
    {
        return $this->formatter->asArray();
    }

    // Formatting methods
    public function asClass(?string $entityClass = null): mixed
    {
        return $this->formatter->asClass($entityClass);
    }

    public function asColumn(int $columnIndex = 0): array
    {
        return $this->formatter->asColumn($columnIndex);
    }

    public function asKeyPairs(): array
    {
        return $this->formatter->asKeyPairs();
    }

    public function asObject(): mixed
    {
        return $this->formatter->asObject();
    }

    /**
     * Close the cursor to free up resources.
     */
    public function close(): void
    {
        if (isset($this->pdoStatement)) {
            $this->pdoStatement->closeCursor();
        }
    }

    /**
     * Get the number of rows affected/returned.
     *
     * @return int
     */
    public function count(): int
    {
        $this->ensureExecuted();
        return $this->rowCount;
    }

    public function exists(): bool
    {
        return $this->count() > 0;
    }

    public function first(): mixed
    {
        $this->operation = 'first';
        $this->ensureExecuted();

        if ($this->paginator->getLimit() !== null && $this->paginator->getLimit() > 1) {
            $results = $this->fetcher->fetchAll();
            $limited = array_slice($results, 0, $this->paginator->getLimit());
            return $limited[0] ?? null;
        }

        return $this->fetcher->fetchFirst();
    }

    /**
     * Get iterator for foreach support.
     *
     * @throws QueryResultException
     *
     * @return Traversable
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->all());
    }

    /**
     * Get the last insert ID from database.
     *
     * @param string|null $sequenceName Optional name of the sequence object
     *
     * @throws QueryResultException
     *
     * @return string
     */
    public function getLastInsertId(?string $sequenceName = null): string
    {
        try {
            $id = $this->dataMapper->getConnexion()->open()->lastInsertId($sequenceName);

            if ($id === false) {
                throw new QueryResultException('Failed to retrieve last insert ID');
            }

            return $id;
        } catch (PDOException $exception) {
            throw new QueryResultException(
                'Error getting last insert ID: ' . $exception->getMessage(),
                (int) $exception->getCode(),
                $exception,
            );
        }
    }

    public function getOperation(): string
    {
        return $this->operation;
    }

    public function getEntity(): Entity
    {
        return $this->entity;
    }

    // =========================================
    // BACKWARD COMPATIBILITY METHODS
    // =========================================

    /**
     * Backward compatibility with old getResults() method.
     *
     * @deprecated Use execute() method instead
     */
    public function getResults(string|array|null $params = null, ?string $className = null): self
    {
        $fetchOptions = $this->config->convertLegacyParams($params, $className, $this->entity);
        return $this->execute($fetchOptions);
    }

    /**
     * Get row count from the query.
     *
     * @return int
     */
    public function getRowCount(): int
    {
        return $this->rowCount;
    }

    /**
     * Check if the result set is empty.
     *
     * @throws QueryResultException
     *
     * @return bool
     */
    public function isEmpty(): bool
    {
        $this->ensureExecuted();
        return $this->rowCount === 0;
    }

    public function setLastLimit(int $limit): QueryResultPaginator
    {
        return $this->paginator->setLastLimit($limit);
    }

    // Pagination methods
    public function setLimit(int $limit): QueryResultPaginator
    {
        return $this->paginator->setLimit($limit);
    }

    /**
     * @param string $operation
     *
     * @return QueryResult
     */
    public function setOperation(string $operation): self
    {
        $this->operation = $operation;
        return $this;
    }

    public function setPagination(int $page, int $perPage): QueryResultPaginator
    {
        return $this->paginator->setPagination($page, $perPage);
    }

    /**
     * Get the boolean result of the query execution.
     *
     * @return bool
     */
    public function wasSuccessful(): bool
    {
        return $this->dataMapper->getQueryResult();
    }

    private function fetchResults(string $operation, ?int $limit = null): mixed
    {
        $this->ensureExecuted();
        return $this->fetcher->fetchResults($operation, $limit);
    }

    private function page(int $page, int $perPage): array
    {
        $this->ensureExecuted();
        return $this->fetcher->fetchPage($page, $perPage);
    }

    /**
     * Ensure query has been executed.
     *
     * @throws QueryResultException
     */
    private function ensureExecuted(): void
    {
        if (!$this->isExecuted) {
            throw new QueryResultException('Query must be executed before accessing results');
        }
    }

    private function initializeComponents(): void
    {
        $this->config = new QueryResultConfig($this->entity);
        $this->paginator = new QueryResultPaginator();
    }

    private function initializeQueryStatement(): void
    {
        try {
            $this->pdoStatement = $this->dataMapper->getQueryStatement();
            $this->fetcher = new QueryResultFetcher($this->pdoStatement, $this->config);
            $this->formatter = new QueryResultFormatter($this, $this->config);
        } catch (Throwable $exception) {
            throw new QueryResultException(
                'Failed to initialize QueryResult: ' . $exception->getMessage(),
                0,
                $exception,
            );
        }
    }
}