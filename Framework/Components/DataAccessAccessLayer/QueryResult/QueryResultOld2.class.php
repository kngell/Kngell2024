<?php

declare(strict_types=1);

/**
 * Modern QueryResult with improved type safety, better naming, and maintained compatibility.
 * Preserves the critical execution flow that makes PDO FETCH_CLASS work with entities.
 */
class QueryResultOld2 implements Countable, IteratorAggregate
{
    /** @var array<string, int> Map of return types to PDO fetch constants */
    private const FETCH_MODE_MAP = [
        'array' => PDO::FETCH_ASSOC,
        'object' => PDO::FETCH_OBJ,
        'class' => PDO::FETCH_CLASS,
        'column' => PDO::FETCH_COLUMN,
        'key_pair' => PDO::FETCH_KEY_PAIR,
    ];

    private ?string $className = null;
    private ?array $constructorArgs = null;
    private string $fetchMode = 'array';
    private bool $isExecuted = false;

    /** @var PDOStatement The PDO statement object */
    private PDOStatement $pdoStatement;

    private mixed $results = null;
    private int $rowCount = 0;

    /**
     * @param DataMapperInterface $dataMapper The data mapper interface
     * @param Entity $entity The entity model for context
     *
     * @throws QueryResultException If unable to get query statement
     */
    public function __construct(
        private DataMapperInterface $dataMapper,
        private Entity $entity,
    ) {
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

    /**
     * Execute the query with specified fetch options.
     *
     * @param string|array|null $fetchOptions Fetch configuration
     *
     * @throws QueryResultException
     *
     * @return self
     */
    public function execute(string|array|null $fetchOptions = null): self
    {
        if ($this->isExecuted) {
            throw new QueryResultException('Query has already been executed');
        }

        try {
            $this->processFetchOptions($fetchOptions);
            $this->configureFetchMode();
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

    /**
     * Get all records from result set.
     *
     * @throws QueryResultException
     *
     * @return array
     */
    public function getAll(): array
    {
        $this->ensureExecuted();

        try {
            $results = $this->pdoStatement->fetchAll();
            return is_array($results) ? $results : [];
        } catch (PDOException $exception) {
            throw new QueryResultException(
                'Failed to fetch all results: ' . $exception->getMessage(),
                (int) $exception->getCode(),
                $exception,
            );
        }
    }

    /**
     * Get a column as an array.
     *
     * @param int $columnIndex Column index (default: 0)
     *
     * @throws QueryResultException
     *
     * @return array
     */
    public function getColumn(int $columnIndex = 0): array
    {
        $this->ensureExecuted();

        try {
            $results = $this->pdoStatement->fetchAll(PDO::FETCH_COLUMN, $columnIndex);
            return is_array($results) ? $results : [];
        } catch (PDOException $exception) {
            throw new QueryResultException(
                'Failed to fetch column: ' . $exception->getMessage(),
                (int) $exception->getCode(),
                $exception,
            );
        }
    }

    /**
     * Get first record from result set.
     *
     * @throws QueryResultException
     *
     * @return array|object|null
     */
    public function getFirst(): array|object|null
    {
        $this->ensureExecuted();

        try {
            $results = $this->pdoStatement->fetchAll();
            return $results[0] ?? null;
        } catch (PDOException $exception) {
            throw new QueryResultException(
                'Failed to fetch first result: ' . $exception->getMessage(),
                (int) $exception->getCode(),
                $exception,
            );
        }
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
        return new ArrayIterator($this->getAll());
    }

    /**
     * Get results as key-value pairs.
     *
     * @throws QueryResultException
     *
     * @return array
     */
    public function getKeyPairs(): array
    {
        $this->ensureExecuted();

        try {
            $results = $this->pdoStatement->fetchAll(PDO::FETCH_KEY_PAIR);
            return is_array($results) ? $results : [];
        } catch (PDOException $exception) {
            throw new QueryResultException(
                'Failed to fetch key pairs: ' . $exception->getMessage(),
                (int) $exception->getCode(),
                $exception,
            );
        }
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
        $fetchOptions = $this->convertLegacyParams($params, $className);
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
     * Get a single record.
     *
     * @throws QueryResultException
     *
     * @return array|object|null
     */
    public function getSingle(): array|object|null
    {
        $this->ensureExecuted();

        try {
            $result = $this->pdoStatement->fetch();
            return $result ?: null;
        } catch (PDOException $exception) {
            throw new QueryResultException(
                'Failed to fetch single result: ' . $exception->getMessage(),
                (int) $exception->getCode(),
                $exception,
            );
        }
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

    /**
     * Get the boolean result of the query execution.
     *
     * @return bool
     */
    public function wasSuccessful(): bool
    {
        return $this->dataMapper->getQueryResult();
    }

    /**
     * Configure the PDO fetch mode - CRITICAL: Must be called before any data access.
     */
    private function configureFetchMode(): void
    {
        $pdoFetchMode = self::FETCH_MODE_MAP[$this->fetchMode] ?? PDO::FETCH_ASSOC;

        try {
            if ($this->className !== null) {
                if ($this->constructorArgs !== null) {
                    $this->pdoStatement->setFetchMode($pdoFetchMode, $this->className, $this->constructorArgs);
                } else {
                    $this->pdoStatement->setFetchMode($pdoFetchMode, $this->className);
                }
            } else {
                $this->pdoStatement->setFetchMode($pdoFetchMode);
            }
        } catch (PDOException $exception) {
            // Fallback to associative array on error
            $this->pdoStatement->setFetchMode(PDO::FETCH_ASSOC);
            throw new QueryResultException(
                'Failed to set fetch mode: ' . $exception->getMessage(),
                (int) $exception->getCode(),
                $exception,
            );
        }
    }

    /**
     * Convert legacy parameters to new fetch options format.
     */
    private function convertLegacyParams(string|array|null $params, ?string $className): ?array
    {
        if ($params === null) {
            return null;
        }

        if (is_string($params)) {
            $fetchOptions = ['mode' => $params];
            if ($params === 'class') {
                $fetchOptions['class'] = $className ?? $this->entity::class;
            }
            return $fetchOptions;
        }

        if (is_array($params)) {
            return $params;
        }

        return null;
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

    /**
     * Initialize the query statement.
     */
    private function initializeQueryStatement(): void
    {
        try {
            $this->pdoStatement = $this->dataMapper->getQueryStatement();
        } catch (Throwable $exception) {
            throw new QueryResultException(
                'Failed to initialize QueryResult: ' . $exception->getMessage(),
                0,
                $exception,
            );
        }
    }

    /**
     * Process array-based fetch options.
     */
    private function processArrayFetchOptions(array $fetchOptions): void
    {
        if (isset($fetchOptions['mode'])) {
            $this->fetchMode = $fetchOptions['mode'];
        }

        if (isset($fetchOptions['class'])) {
            $this->className = $fetchOptions['class'];
            $this->constructorArgs = $fetchOptions['constructor_args'] ?? null;
        }
    }

    /**
     * Process fetch options.
     */
    private function processFetchOptions(string|array|null $fetchOptions): void
    {
        if ($fetchOptions === null) {
            return;
        }

        if (is_string($fetchOptions)) {
            $this->fetchMode = $fetchOptions;
            if ($fetchOptions === 'class') {
                $this->className = $this->entity::class;
            }
        } elseif (is_array($fetchOptions)) {
            $this->processArrayFetchOptions($fetchOptions);
        }
    }
}