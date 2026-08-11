<?php

declare(strict_types=1);

class SqlDeleteQuery extends SqlQuery implements SqlDeleteQueryBuilderInterface
{
    use MapFragmentTrait;
    use SqlWhereConditionTrait;
    use SqlJoinTrait;

    private const SqlStatement TYPE = SqlStatement::DELETE;

    private array $deleteMap = [];

    public function __construct(
        EntityManagerInterface $em,
        private bool $isBulkQuery = false,
    ) {
        $this->method = self::TYPE->value;
        parent::__construct(null, self::TYPE, $em);
        $this->initializeWithDependencies($em->getTableAliasHelper(), $this->getState());
        $this->initializeComponents();
    }

    public function build(): string
    {
        $this->buildStatement();
        return parent::build();
    }

    public function delete(null|string|Closure $table = null, null|string $alias = null): static
    {
        $this->deleteMap[__FUNCTION__] = [
            'table' => $table,
            'customAlias' => $alias,
        ];
        $this->customAlias = $alias;
        $this->table = $table;

        $this->queryFlow[__FUNCTION__] = true;
        if (!$this->entryMethod === null) {
            $this->entryMethod = __FUNCTION__;
        }
        return $this;
    }

    public function from(mixed $table = null, ?string $alias = null): static
    {
        list($table, $key) = $this->getUniqueTableName(__FUNCTION__, $table, $this->queryMap);
        $this->deleteMap['delete']['table'] = $table;
        $this->deleteMap['delete']['customAlias'] = $alias;
        $this->customAlias = $alias;
        $this->table = $table;
        $this->deleteMap[__FUNCTION__] = $this->standardizer->setMethod(__FUNCTION__)->standardize($this->deleteMap['delete']);
        $this->queryMap[$key] = __FUNCTION__;
        $this->queryFlow[__FUNCTION__] = true;
        return $this;
    }

    public function deleteFrom(null|string|Closure $table = null, null|string $alias = null): static
    {
        if ($table === null) {
            $table = $this->resolveMainTable();
        }

        return $this->delete()->from($table, $alias);
    }

    public function where(mixed ...$conditions): static
    {
        if (!isset($this->queryFlow['from']) && isset($this->queryFlow['delete'])) {
            $this->from();
        }
        if (!isset($this->deleteMap['where'])) {
            $this->deleteMap[__FUNCTION__] = [];
            $this->queryFlow[__FUNCTION__] = true;
        }
        $this->deleteMap[__FUNCTION__][] = $this->standardizer->setMethod(__FUNCTION__)->standardize($conditions);
        return $this;
    }

    public function join(string $table, ?string $alias = null): static
    {
        throw new Exception('Not implemented');
    }

    public function execute(): array
    {
        throw new Exception('Not implemented');
    }

    public function getStatement(): SqlStatement
    {
        return self::TYPE;
    }

    public function assumeEntityManagerhasData(): void
    {
        if (!$this->em->hasData()) {
            throw new QueryFlowException('No data defined for delete');
        }
        $this->queryFlow['delete'] = true;
        $this->queryFlow['from'] = true;
    }

    public function hasDelete(): bool
    {
        return isset($this->queryFlow['delete']);
    }

    public function hasWhere(): bool
    {
        return isset($this->queryFlow['where']);
    }

    public function hasFrom(): bool
    {
        return isset($this->queryFlow['from']);
    }

    public function assumeDeleteCurrentTable(): void
    {
        if (!$this->hasDelete()) {
            $this->deleteFrom($this->resolveMainTable());
        }
    }

    /**
     * @return array
     */
    public function getDeleteMap(): array
    {
        return $this->deleteMap;
    }

    private function buildStatement(): void
    {
        $statement = new DeleteStatement(
            $this->deleteMap,
            $this->queryFlow,
            $this->em,
        );
        $this->add($statement);
    }
}