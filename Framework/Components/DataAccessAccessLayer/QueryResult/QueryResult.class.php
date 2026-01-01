<?php

declare(strict_types=1);

class QueryResult implements Countable, IteratorAggregate
{
    private null|int $lastUpdateId = null;
    private null|int $lastOperationId = null;
    private null|string $entityKeyField = null;
    private QueryResultConfig $config;
    private QueryResultFetcher $fetcher;
    private QueryResultFormatter $formatter;
    private bool $isInitialized = false;
    private string $operation = 'all';
    private QueryResultPaginator $paginator;
    private QueryResultHydrator $hydrator;
    private ?PDOStatement $pdoStatement = null;
    private int $rowCount = 0;
    private ?string $lastInsertId = null;
    private bool $isWriteOperation = false;

    public function __construct(
        private DataMapperInterface $dataMapper,
        private string $entity,
        private array $tableAlias,
        private EntityFactoryInterface $entityFactory,
        private array $tableMap,
    ) {
        $this->initializeComponents();
        // Don't initialize statement yet - wait for first fetch
    }

    public function __destruct()
    {
        $this->close();
    }

    /**
     * Initialize or re-initialize for a new operation.
     */
    public function prepare(string $operation = 'all'): self
    {
        $this->operation = $operation;
        $this->isInitialized = false; // Force re-initialization
        return $this;
    }

    public function all(): array
    {
        $this->initialize();
        $this->operation = 'all';

        $results = $this->fetcher->fetchAll();
        return $this->paginator->applyPagination($results, 'all');
    }

    public function first(): mixed
    {
        $this->initialize();
        $this->operation = 'first';

        if ($this->paginator->getLimit() !== null && $this->paginator->getLimit() > 1) {
            $results = $this->fetcher->fetchAll();
            $limited = array_slice($results, 0, $this->paginator->getLimit());
            return $limited[0] ?? null;
        }

        return $this->fetcher->fetchFirst();
    }

    public function last(): mixed
    {
        $this->initialize();
        $this->operation = 'last';

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
        $this->initialize();
        $this->operation = 'single';
        return $this->fetcher->fetchSingle();
    }

    public function asArray(): mixed
    {
        $this->initialize();
        return $this->formatter->asArray();
    }

    public function asClass(?string $entityClass = null): mixed
    {
        $this->initialize();
        $entityClass = $entityClass ?? $this->entity;
        return $this->formatter->asClass($entityClass);
    }

    public function asColumn(int $columnIndex = 0): array
    {
        $this->initialize();
        return $this->formatter->asColumn($columnIndex);
    }

    public function asKeyPairs(): array
    {
        $this->initialize();
        return $this->formatter->asKeyPairs();
    }

    public function asObject(): mixed
    {
        $this->initialize();
        return $this->formatter->asObject();
    }

    public function count(): int
    {
        $this->initialize();
        if ($this->rowCount === 0) {
            return 0;
        }

        if ($this->rowCount === 1 && $this->pdoStatement->columnCount() === 1) {
            $value = $this->pdoStatement->fetchColumn(0);
            return is_numeric($value) ? (int) $value : $this->rowCount;
        }
        return $this->rowCount;
    }

    public function exists(): bool
    {
        $this->initialize();
        return $this->rowCount > 0;
    }

    public function isEmpty(): bool
    {
        $this->initialize();
        return $this->rowCount === 0;
    }

    public function getRowCount(): int
    {
        return $this->rowCount;
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->all());
    }

    public function isSuccess(): bool
    {
        if ($this->isWriteOperation) {
            if ($this->dataMapper->getExecutionStatus() === false) {
                return false;
            }
            return true;
        }
        return $this->dataMapper->getExecutionStatus() !== false;
    }

    public function wasSuccessful(): bool
    {
        return $this->isSuccess();
    }

    public function getAffectedRows(): int
    {
        return $this->rowCount;
    }

    public function queryExecuted(): bool
    {
        return $this->dataMapper->getExecutionStatus() !== false;
    }

    public function setLastUpdateId(null|int $lastUpdateId): self
    {
        $this->lastUpdateId = $lastUpdateId;
        return $this;
    }

    public function getLastInsertId(?string $sequenceName = null): bool|string
    {
        $this->initialize();

        if ($this->lastInsertId !== null) {
            return $this->lastInsertId;
        }

        try {
            $id = $this->dataMapper->lastInsertId($sequenceName);

            if ($id === false || $id === '0') {
                return '';
            }

            $this->lastInsertId = (string) $id;
            return $this->lastInsertId;
        } catch (PDOException $exception) {
            throw new QueryResultException(
                'Error getting last insert ID: ' . $exception->getMessage(),
                (int) $exception->getCode(),
                $exception,
            );
        }
    }

    public function close(): void
    {
        if ($this->pdoStatement !== null) {
            $this->pdoStatement->closeCursor();
        }
    }

    /**
     * Backward compatibility method.
     */
    public function execute(string|array|null $fetchOptions = null): self
    {
        $this->initialize();

        if ($fetchOptions !== null) {
            $this->config->processFetchOptions($fetchOptions, $this->entity);
        }

        return $this;
    }

    /**
     * Backward compatibility with old getResults() method.
     */
    public function getResults(string|array|null $params = null, ?string $className = null): self
    {
        $fetchOptions = $this->config->convertLegacyParams($params, $className, $this->entity);
        return $this->execute($fetchOptions);
    }

    public function setOperation(string $operation): self
    {
        $this->operation = $operation;
        return $this;
    }

    public function getOperation(): string
    {
        return $this->operation;
    }

    public function setLimit(int $limit): self
    {
        $this->paginator->setLimit($limit);
        return $this;
    }

    public function setLastLimit(int $limit): self
    {
        $this->paginator->setLastLimit($limit);
        return $this;
    }

    public function setPagination(int $page, int $perPage): self
    {
        $this->paginator->setPagination($page, $perPage);
        return $this;
    }

    public function getConfig(): QueryResultConfig
    {
        return $this->config;
    }

    public function fetchColumn(int $columnIndex = 0): array
    {
        $this->initialize();
        return $this->fetcher->fetchColumn($columnIndex);
    }

    public function fetchKeyPairs(): array
    {
        $this->initialize();
        return $this->fetcher->fetchKeyPairs();
    }

    /**
     * @return string
     */
    public function getEntity(): string
    {
        return $this->entity;
    }

    public function getQeuryString(): string
    {
        return $this->dataMapper->getQueryString();
    }

    public function getQueryParameters(): array
    {
        return $this->dataMapper->getQueryParameters();
    }

    /**
     * @return null|int
     */
    public function getLastUpdateId(): ?int
    {
        return $this->lastUpdateId;
    }

    /**
     * @return null|int
     */
    public function getLastOperationId(): ?int
    {
        return $this->lastOperationId;
    }

    /**
     * @param null|int $lastOperationId
     *
     * @return QueryResult
     */
    public function setLastOperationId(?int $lastOperationId): QueryResult
    {
        $this->lastOperationId = $lastOperationId;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getEntityKeyField(): ?string
    {
        return $this->entityKeyField;
    }

    /**
     * @param null|string $entityKeyField
     *
     * @return QueryResult
     */
    public function setEntityKeyField(?string $entityKeyField): QueryResult
    {
        $this->entityKeyField = $entityKeyField;

        return $this;
    }

    public function hasResults(): bool
    {
        if (!$this->queryExecuted()) {
            return false;
        }

        $this->initialize();
        return $this->rowCount > 0;
    }

    public function insertSucceeded(): bool
    {
        if (!$this->queryExecuted()) {
            return false;
        }
        return $this->isWriteOperation && $this->isSuccess() && $this->rowCount > 0;
    }

    public function updateSucceeded(): bool
    {
        if (!$this->queryExecuted()) {
            return false;
        }
        return true;
    }

    public function deleteSucceeded(): bool
    {
        if (!$this->queryExecuted()) {
            return false;
        }
        return true;
    }

    private function initialize(): void
    {
        if ($this->isInitialized) {
            return;
        }

        try {
            $this->pdoStatement = $this->dataMapper->getQueryStatement();
            if (!$this->pdoStatement) {
                throw new QueryResultException('PDOStatement not available from DataMapper');
            }
            $this->rowCount = $this->pdoStatement->rowCount();
            $queryString = trim($this->getQeuryString());
            $firstWord = strtoupper(explode(' ', $queryString)[0] ?? '');
            $this->isWriteOperation = in_array($firstWord, ['INSERT', 'UPDATE', 'DELETE', 'REPLACE']);

            if ($this->isWriteOperation && $firstWord === 'INSERT') {
                $this->lastInsertId = $this->dataMapper->lastInsertId() ?: null;
            }

            $this->fetcher = new QueryResultFetcher(
                $this->pdoStatement,
                $this->config,
                $this->hydrator,
                $this->entityFactory,
            );

            // Initialize formatter
            $this->formatter = new QueryResultFormatter(
                $this,
                $this->config,
                $this->entityFactory->getChangeTracker(),
                $this->entityFactory->getNormalizer(),
            );
            $this->formatter->setTableAlias($this->tableAlias);
            $this->isInitialized = true;
        } catch (Throwable $exception) {
            throw new QueryResultException(
                'Failed to initialize QueryResult: ' . $exception->getMessage(),
                0,
                $exception,
            );
        }
    }

    private function initializeComponents(): void
    {
        $this->config = new QueryResultConfig(
            $this->entity,
            $this->tableAlias,
            $this->tableMap,
        );
        $this->config->setConstructorArgs(
            [
                $this->entityFactory->getDependencies(),
                $this->tableAlias,
                $this->tableMap,
            ],
        );
        $this->paginator = new QueryResultPaginator();
        $this->hydrator = new QueryResultHydrator(
            $this->entityFactory->getChangeTracker(),
            $this->entityFactory->getNormalizer(),
        );
    }
}