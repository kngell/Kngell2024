<?php

declare(strict_types=1);

class SqlInsertQuery extends SqlQuery implements SqlInsertQueryBuilderInterface
{
    private array $insertMap = [];
    private array $columns = [];
    private array $values = [];
    private bool $isClosure = false;

    public function __construct(private EntityManagerInterface $em)
    {
        parent::__construct(SqlStatementType::INSERT);
        $this->initializeWithDependencies($em->getTableAliasHelper(), $this->getState());
    }

    public function build(): string
    {
        $this->initializeComponents();

        $this->flowValidator->validate($this->queryFlow, $this->insertMap);
        $this->clauseBuilder->buildAllClauses();

        return parent::build();
    }

    public function insertInto(string $table): self
    {
        $this->queryFlow[__FUNCTION__] = true;
        return $this->into($table);
    }

    public function insert(mixed ...$data): self
    {
        $data = ArrayUtils::first($data);
        $this->insertMap[__FUNCTION__] = $data;
        $this->queryFlow[__FUNCTION__] = true;
        return $this;
    }

    public function columns(string|array ...$columns): self
    {
        try {
            $columns = ArrayUtils::first($columns);

            if (empty($columns)) {
                throw new InvalidArgumentException('Please provide columns to insert');
            }

            if (!ArrayUtils::isStringList($columns)) {
                throw new InvalidArgumentException('You should insert a list of columns as string');
            }
            $this->insertMap[__FUNCTION__] = $columns;
            $this->queryFlow[__FUNCTION__] = true;
            return $this;
        } catch (QueryFlowException $th) {
            throw new QueryFlowException('Unable to insert columns that are not well formatted');
        }
    }

    public function into(string $table): self
    {
        if (empty($table)) {
            throw new InvalidArgumentException('Cannot insert into empty table');
        }
        $this->table = $table;
        $this->insertMap[__FUNCTION__] = $table;
        $this->queryFlow[__FUNCTION__] = true;
        return $this;
    }

    public function values(mixed ...$data): self
    {
        try {
            $data = ArrayUtils::first($data);
            if (empty($data)) {
                throw new InvalidArgumentException('Please provide values to insert');
            }
            $this->values = $data;
            $this->insertMap[__FUNCTION__] = $data;
            $this->queryFlow[__FUNCTION__] = true;
            return $this;
        } catch (QueryFlowException $th) {
            throw new QueryFlowException('Unable to insert data without proper values');
        }
    }

    public function fromSelect(SqlSelectQueryBuilderInterface $selectQuery): self
    {
        $this->insertMap[__FUNCTION__] = $selectQuery;
        $this->queryFlow[__FUNCTION__] = true;
        return $this;
    }

    public function onDuplicateKeyUpdate(array $updates): self
    {
        return $this;
    }

    public function ignore(): self
    {
        return $this;
    }

    public function execute(): array
    {
        return [];
    }

    public function getStatementType(): SqlStatementType
    {
        return $this->sqlClause;
    }

    public function hasInsert(): bool
    {
        return isset($this->queryFlow['insert']);
    }

    public function hasInto(): bool
    {
        return isset($this->queryFlow['into']);
    }

    public function hasColumns(): bool
    {
        return isset($this->queryFlow['columns']);
    }

    public function assumeInsertIntoCurrentTable(): void
    {
        $entity = $this->em->getEntity();
        if (!$this->hasInto()) {
            $this->into($this->resolveMainTable($entity));
        }
    }

    public function assumeEntityManagerHasInsertData(): void
    {
        if (!$this->em->hasData()) {
            throw new QueryFlowException('No data defined to insert into the data base');
        }
        $this->insertMap['insert'] = $this->em->getEntityData();
        $this->queryFlow['insert'] = true;
    }

    public function assumeAllColumns(): void
    {
        if (!$this->hasColumns()) {
            $this->insert([]);
        }
    }

    /**
     * @return bool
     */
    public function isClosure(): bool
    {
        return $this->isClosure;
    }

    public function getDatabaseDefaultColumns(): SqlQueryComponent
    {
        $builder = new SqlSelectQuery($this->em);
        return $builder->select(
            'COLUMN_NAME',
            'DATA_TYPE',
            'COLUMN_DEFAULT',
            'IS_NULLABLE',
            'EXTRA',
        )->from('INFORMATION_SCHEMA.COLUMNS')
        ->where('TABLE_SCHEMA', 'DATABASE()')
        ->and('TABLE_NAME', $this->table)
        ->orderBy('ORDINAL_POSITION');
    }

    public function getInsertMapFragments(array $insertMap): array
    {
        $table = isset($insertMap['into']) && !ArrayUtils::isDeepEmpty($insertMap['into']) ? $insertMap['into'] : null;

        $insertData = isset($insertMap['insert']) && !ArrayUtils::isDeepEmpty($insertMap['insert']) ? $insertMap['insert'] : null;
        $columnsData = isset($insertMap['columns']) && !ArrayUtils::isDeepEmpty($insertMap['columns']) ? $insertMap['columns'] : null;
        $valuesData = isset($insertMap['values']) && !ArrayUtils::isDeepEmpty($insertMap['values']) ? $insertMap['values'] : null;

        return [$table, $insertData, $columnsData, $valuesData];
    }

    /**
     * @return array
     */
    public function getInsertMap(): array
    {
        return $this->insertMap;
    }

    /**
     * @return EntityManagerInterface
     */
    public function getEntityManager(): EntityManagerInterface
    {
        return $this->em;
    }
}