<?php

declare(strict_types=1);

/**
 * QueryResult Class.
 *
 * Handles the storage and rendering of database query results
 */
class QueryResult implements Countable
{
    /** @var array<string, int> Map of return types to PDO fetch constants */
    private const FETCH_TYPE_MAP = [
        'object' => PDO::FETCH_OBJ,
        'class' => PDO::FETCH_CLASS,
    ];
    /** @var mixed Store for query results */
    private mixed $results;

    /** @var int Number of rows affected/returned */
    private int $rowCount = 0;

    /** @var PDOStatement The PDO statement object */
    private PDOStatement $_query;

    /** @var string|null Type of return value expected */
    private ?string $returnType = null;

    /**
     * Constructor.
     *
     * @param DataMapperInterface $mapper The data mapper interface
     * @param Entity $entity The entity model
     * @throws RuntimeException If unable to get query statement
     */
    public function __construct(private DataMapperInterface $mapper, private Entity $entity)
    {
        try {
            $this->_query = $mapper->getQueryStatement();
        } catch (Throwable $e) {
            throw new RuntimeException('Failed to initialize QueryResult: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Get the last insert ID from database.
     *
     * @param string|null $name Optional name of the sequence object
     * @return string|false The last insert ID or false on failure
     */
    public function getLastInsertId(string|null $name = null): string|false
    {
        try {
            return $this->mapper->getConnexion()->open()->lastInsertId($name);
        } catch (PDOException $e) {
            $this->logError('Error getting last insert ID', $e);
            return false;
        }
    }

    /**
     * Set up and execute the query to get results.
     *
     * @param string|array|null $params Optional parameters for fetching
     * @param string|null $className Optional class name for object mapping
     * @return self
     */
    public function getResults(string|array|null $params = null, string|null $className = null): self
    {
        try {
            [$mode, $className, $constructorArgs] = $this->params($params, $className);
            $mode = $this->returnType($mode);
            $this->fetchMode($mode, $className, $constructorArgs);
            $this->rowCount = $this->_query->rowCount();
            return $this;
        } catch (PDOException $e) {
            $this->logError('Error getting query results', $e);
            $this->rowCount = 0;
            return $this;
        }
    }

    /**
     * Check if the result set is empty.
     *
     * @return bool True if empty, false otherwise
     */
    public function isEmpty(): bool
    {
        return $this->_query->rowCount() === 0;
    }

    /**
     * Get row count as results.
     * Implements Countable interface.
     *
     * @return int Number of rows
     */
    public function count(): int
    {
        $this->returnType = 'count';
        $this->results = $this->readResults();
        return (int) $this->results;
    }

    /**
     * Get a single record.
     *
     * @return array|object|null A single record or null if none found
     */
    public function single(): array|object|null
    {
        $this->returnType = 'single';
        $this->results = $this->readResults();
        return $this->results;
    }

    /**
     * Get first record from result set.
     *
     * @return array|object|null First record or null if none found
     */
    public function first(): array|object|null
    {
        $this->returnType = 'first';
        $this->results = $this->readResults();
        return $this->results;
    }

    /**
     * Get all records from result set.
     *
     * @return array All records
     */
    public function all(): array
    {
        $this->returnType = 'all';
        $this->results = $this->readResults();
        return (array) $this->results;
    }

    /**
     * Get row count from the query.
     *
     * @return int Number of rows
     */
    public function rowCount(): int
    {
        return $this->rowCount;
    }

    /**
     * Get the boolean result of the query execution.
     *
     * @return bool True if query was successful, false otherwise
     */
    public function getQueryResult(): bool
    {
        return $this->mapper->getQueryResult();
    }

    /**
     * Process parameters based on type.
     *
     * @param string|array|null $params The parameters to process
     * @param string|null $className Optional class name
     * @return array [mode, className, constructorArgs]
     */
    private function params(string|array|null $params = null, string|null $className = null): array
    {
        return match (true) {
            is_string($params) => $this->stringOptions($params, $className),
            is_array($params) => $this->arrayOptions($params),
            default => ['', null, null],
        };
    }

    /**
     * Process string parameters.
     *
     * @param string $params The string parameters
     * @param string|null $className Optional class name
     * @return array [mode, className, constructorArgs]
     */
    private function stringOptions(string $params, string|null $className = null): array
    {
        if ($params === 'class') {
            $className = $className ?? $this->entity::class;
        }
        return [$params, $className, null];
    }

    /**
     * Process array parameters.
     *
     * @param array $params The array parameters
     * @return array [mode, className, constructorArgs]
     */
    private function arrayOptions(array $params): array
    {
        if (array_key_exists('class', $params)) {
            $constructorArgs = $params['constructorArgs'] ?? null;
            return [key($params), $params[key($params)], $constructorArgs];
        }
        return ['', null, null];
    }

    /**
     * Read results based on return type.
     *
     * @return mixed The query results
     */
    private function readResults(): mixed
    {
        try {
            return match ($this->returnType) {
                'count' => $this->_query->rowCount(),
                'single' => $this->_query->fetch(),
                'first' => $this->getFirstRecord(),
                'all' => $this->_query->fetchAll(),
                default => $this->_query->fetchAll()
            };
        } catch (PDOException $e) {
            $this->logError('Error reading results', $e);
            return match ($this->returnType) {
                'count' => 0,
                'single', 'first' => null,
                default => []
            };
        }
    }

    /**
     * Get the first record from fetchAll results safely.
     *
     * @return array|object|null First record or null if none
     */
    private function getFirstRecord(): array|object|null
    {
        $results = $this->_query->fetchAll();
        return ! empty($results) ? $results[0] : null;
    }

    /**
     * Set the fetch mode for the query.
     *
     * @param int $type The PDO fetch mode
     * @param string|null $className Optional class name for object mapping
     * @param array|null $constructorArgs Optional constructor arguments
     * @return void
     */
    private function fetchMode(int $type, string|null $className = null, ?array $constructorArgs = null): void
    {
        try {
            if ($className !== null) {
                if ($constructorArgs !== null) {
                    $this->_query->setFetchMode($type, $className, $constructorArgs);
                } else {
                    $this->_query->setFetchMode($type, $className);
                }
            } else {
                $this->_query->setFetchMode($type);
            }
        } catch (PDOException $e) {
            $this->logError('Error setting fetch mode', $e);
            $this->_query->setFetchMode(PDO::FETCH_ASSOC);
        }
    }

    /**
     * Convert string return type to PDO fetch constant.
     *
     * @param string|null $type The string type
     * @return int The PDO fetch constant
     */
    private function returnType(?string $type): int
    {
        return self::FETCH_TYPE_MAP[$type] ?? PDO::FETCH_ASSOC;
    }

    /**
     * Log error with standardized format.
     *
     * @param string $message Error message prefix
     * @param Throwable $exception The exception that occurred
     * @return void
     */
    private function logError(string $message, Throwable $exception): void
    {
        error_log(sprintf(
            '%s: %s [File: %s, Line: %d]',
            $message,
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine()
        ));
    }
}