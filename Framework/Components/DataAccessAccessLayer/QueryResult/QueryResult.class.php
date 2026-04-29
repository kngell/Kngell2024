<?php

declare(strict_types=1);

class QueryResult implements Countable, IteratorAggregate
{
    use QueryResultFetchTrait;
    use QueryResultFormatTrait;
    use QueryResultStatusTrait;

    private null|int|array $lastUpdateId = null;
    private null|int $lastOperationId = null;
    private null|string $entityKeyField = null;
    private QueryResultConfig $config;
    private QueryResultFetcher $fetcher;
    private QueryResultFormatter $formatter;
    private bool $isInitialized = false;
    private string $operation = 'all';
    private QueryResultPaginator $paginator;
    private QueryResultHydrator $hydrator;
    private int $rowCount = 0;
    private ?string $lastInsertId = null;
    private bool $isWriteOperation = false;
    private bool $isSkipped = false;
    private bool $executionStatus = false;
    private string $skipReason = '';
    private FetchStrategy $fetchStrategy = FetchStrategy::STANDARD;

    public function __construct(
        private DataMapperInterface $dataMapper,
        private ?PDOStatement $pdoStatement,
        private string $entityClass,
        private array $tableAlias,
        private EntityFactoryInterface $entityFactory,
        private array $tableMap,
        private ?SqlStatement $statementType,
    ) {
        $this->initializeComponents();
        $this->executionStatus = $dataMapper->getExecutionStatus();
    }

    public function __destruct()
    {
        $this->close();
    }

    public function getSqlOperation(): ?SqlStatement
    {
        return $this->statementType;
    }

    public function getAffectedRows(): int
    {
        $this->initialize();
        return $this->pdoStatement ? $this->pdoStatement->rowCount() : 0;
    }

    public function prepare(string $operation = 'all'): self
    {
        $this->operation = $operation;
        $this->isInitialized = false;
        return $this;
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

    public function setSkipped(bool $skipped, string $reason = ''): self
    {
        $this->isSkipped = $skipped;
        $this->skipReason = $reason;
        return $this;
    }

    public function setLastUpdateId(null|int|array $lastUpdateId): self
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
            $this->config->processFetchOptions($fetchOptions, $this->entityClass);
        }

        return $this;
    }

    /**
     * Backward compatibility with old getResults() method.
     */
    public function getResults(string|array|null $params = null, ?string $className = null): self
    {
        $fetchOptions = $this->config->convertLegacyParams($params, $className, $this->entityClass);
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
        return $this->entityClass;
    }

    public function getQueryString(): string
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

    public function hydrateWithRelations(array $data): Object
    {
        return $this->hydrator->hydrateWithRelationships($data);
    }

    /**
     * @param FetchStrategy $fetchStrategy
     *
     * @return QueryResult
     */
    public function setFetchStrategy(FetchStrategy $fetchStrategy): QueryResult
    {
        $this->fetchStrategy = $fetchStrategy;

        return $this;
    }

    /**
     * @return FetchStrategy
     */
    public function getFetchStrategy(): FetchStrategy
    {
        return $this->fetchStrategy;
    }

    /**
     * @param int $rowCount
     *
     * @return QueryResult
     */
    public function setRowCount(int $rowCount): QueryResult
    {
        $this->rowCount = $rowCount;

        return $this;
    }

    /**
     * @return string
     */
    public function getSkipReason(): string
    {
        return $this->skipReason;
    }

    /**
     * @param string $skipReason
     *
     * @return QueryResult
     */
    public function setSkipReason(string $skipReason): QueryResult
    {
        $this->skipReason = $skipReason;

        return $this;
    }

    private function initialize(): void
    {
        if ($this->isInitialized) {
            return;
        }

        try {
            if ($this->wasSkipped()) {
                $this->isInitialized = true;
                return;
            }
            if (!$this->pdoStatement) {
                throw new QueryResultException('PDOStatement not available from DataMapper');
            }

            $this->rowCount = $this->pdoStatement->rowCount();
            $queryString = trim($this->getQueryString());
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
                $this->entityFactory,
                new CartesianHydrator($this->entityFactory),
                new CartesianDetector(),
                $this->entityClass,
            );
            // $this->formatter->setTableAlias($this->tableAlias);
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
            $this->entityClass,
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
            $this->config,
            $this->entityFactory,
            $this->entityClass,
        );
    }
}