<?php

declare(strict_types=1);

class SqlInsertQuery extends SqlQuery implements SqlInsertQueryBuilderInterface
{
    use MapFragmentTrait;

    private const SqlStatement TYPE = SqlStatement::INSERT;

    private array $insertMap = [];
    private ProcessedInsertData $processedData;

    public function __construct(EntityManagerInterface $em, private bool $isBulkQuery = false)
    {
        $this->method = self::TYPE->value;
        parent::__construct(null, self::TYPE, $em);
        $this->initializeWithDependencies($em->getTableAliasHelper(), $this->getState());
        $this->initializeComponents();
    }

    public function build(): string
    {
        $this->ValidateInsertMap();
        $this->initializeProperties();
        $this->buildStatement();
        return parent::build();
    }

    public function insertInto(string $table): self
    {
        $this->queryFlow[__FUNCTION__] = true;
        if (!$this->entryMethod === null) {
            $this->entryMethod = __FUNCTION__;
        }
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
        if (!$this->entryMethod === null) {
            $this->entryMethod = __FUNCTION__;
        }

        return $this;
    }

    public function columns(string|array ...$columns): self
    {
        try {
            if (isset($this->insertMap['columns'])) {
                throw new InvalidArgumentException('Columns are already set');
            }
            $this->insertMap['columns'] = $this->standardizer->setMethod(__FUNCTION__)->setMap($this->insertMap)->standardize($columns);
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
        $table = isset($insertMap['into']) ? $insertMap['into'] : null;

        $insertData = isset($insertMap['insert']) ? $insertMap['insert'] : null;
        $columnsData = isset($insertMap['columns']) ? $insertMap['columns'] : null;
        $valuesData = isset($insertMap['values']) ? $insertMap['values'] : null;

        return [$table, $insertData, $columnsData, $valuesData];
    }

    /**
     * @return array
     */
    public function getInsertMap(): array
    {
        return $this->insertMap;
    }

    private function ValidateInsertMap(): void
    {
        if ($this->hasInsert() && !$this->hasInto()) {
            $this->assumeInsertIntoCurrentTable();
        }
        $insertData = $this->insertMap['insert']->getData() ?? null;
        if ($this->hasInsert() && !$this->hasValues()) {
            $this->assumeInsertDataHasInsertValues();
        } else {
            throw new InvalidArgumentException('No values are defined for columns :' . implode(', ', $insertData['insert']));
        }

        // Validate minimal requirements
        if (!$this->isClosure() && !$this->hasInto()) {
            throw new QueryFlowException('INSERT query requires INTO clause or entity with table definition.');
        }
    }

    private function initializeProperties(): void
    {
        $processor = new InsertDataProcessor($this, $this->insertMap);
        $this->processedData = $processor->process();
    }

    private function buildStatement(): void
    {
        $statement = new InsertStatement(
            $this->insertMap,
            $this->queryFlow,
            $this->em,
            $this->processedData,
        );
        $this->add($statement);
    }

    // /**
    //  * @return EntityManagerInterface
    //  */
    // public function getEntityManager(): EntityManagerInterface
    // {
    //     return $this->em;
    // }
}