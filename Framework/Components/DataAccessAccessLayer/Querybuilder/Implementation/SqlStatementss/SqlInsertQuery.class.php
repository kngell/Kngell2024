<?php

declare(strict_types=1);

class SqlInsertQuery extends SqlQuery implements SqlInsertQueryBuilderInterface
{
    private const SqlStatementType TYPE = SqlStatementType::INSERT;

    private array $insertMap = [];
    private bool $isClosure = false;

    public function __construct(EntityManagerInterface $em)
    {
        $this->method = self::TYPE->value;
        parent::__construct(null, self::TYPE, $em);
        $this->initializeWithDependencies($em->getTableAliasHelper(), $this->getState());
        $this->initializeComponents();
    }

    public function build(): string
    {
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
        if (count($data) === 0) {
            $this->queryFlow['insert'] = true;
            return $this;
        }

        $standardized = $this->standardizer
            ->setMethod('insert')
            ->standardize($data);

        $this->insertMap['insert'] = $standardized;
        $this->queryFlow['insert'] = true;

        return $this;
    }

    public function columns(string|array ...$columns): self
    {
        try {
            $flattenedColumns = ArrayUtils::flattenArrayRecursive($columns);

            if (empty($flattenedColumns)) {
                throw new InvalidArgumentException('Please provide columns to insert');
            }
            if (isset($this->insertMap['columns'])) {
                throw new InvalidArgumentException('Columns are already set');
            }

            if (!ArrayUtils::isStringList($flattenedColumns)) {
                throw new InvalidArgumentException('All columns must be strings');
            }

            $this->insertMap['columns'] = $flattenedColumns;
            $this->queryFlow['columns'] = true;
            return $this;
        } catch (InvalidArgumentException $e) {
            throw new QueryFlowException('Unable to insert columns: ' . $e->getMessage(), $e->getCode());
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
        if (count($data) === 0) {
            throw new InvalidArgumentException('values() cannot be empty');
        }

        $standardized = $this->standardizer
            ->setMethod('values')
            ->setMap($this->insertMap)
            ->standardize($data);

        $this->insertMap['values'] = $standardized;
        $this->queryFlow['values'] = true;

        return $this;
    }

    public function fromSelect(SqlSelectQueryBuilderInterface $selectQuery): self
    {
        $this->insertMap['fromSelect'] = $selectQuery;
        $this->queryFlow['fromSelect'] = true;
        return $this;
    }

    public function onDuplicateKeyUpdate(array $updates): self
    {
        $this->insertMap['onDuplicateKeyUpdate'] = $updates;
        $this->queryFlow['onDuplicateKeyUpdate'] = true;
        return $this;
    }

    public function ignore(): self
    {
        $this->insertMap['ignore'] = true;
        $this->queryFlow['ignore'] = true;
        return $this;
    }

    public function execute(): array
    {
        $sql = $this->build();
        return $this->em->persist()->getQueryResult()->setOperation('all')->asClass();
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

    public function hasValues(): bool
    {
        return isset($this->queryFlow['values']);
    }

    public function hasColumns(): bool
    {
        return isset($this->queryFlow['columns']);
    }

    public function assumeInsertIntoCurrentTable(): void
    {
        if (!$this->hasInto()) {
            $this->into($this->resolveMainTable());
        }
    }

    public function assumeEntityManagerHasInsertData(): void
    {
        if (!$this->em->hasData()) {
            throw new QueryFlowException('No data defined to insert into the data base');
        }
        $this->queryFlow['insert'] = true;
        $this->queryFlow['into'] = true;
        $this->queryFlow['values'] = true;
    }

    public function assumeInsertDataHasInsertValues(): void
    {
        $this->queryFlow['values'] = true;
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

    public function getDatabaseDefaultColumns(): SqlComponent
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