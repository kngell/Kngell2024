<?php

declare(strict_types=1);

class SqlUpdateQuery extends SqlQuery implements SqlUpdateQueryBuilderInterface
{
    use SqlJoinTrait;
    use SqlWhereConditionTrait;

    private const SqlStatement TYPE = SqlStatement::UPDATE;

    private null|BulkUpdateType $bulkType = null;
    private array $updateMap = [];

    public function __construct(EntityManagerInterface $em, private bool $isBulkQuery = false)
    {
        $this->method = self::TYPE->value;
        parent::__construct(null, self::TYPE, $em);
        $this->initializeWithDependencies($em->getTableAliasHelper(), $this->getState());
        $this->initializeComponents();
    }

    #[Override]
    public function from(mixed $table = null, ?string $alias = null): static
    {
        throw new Exception('Not implemented');
    }

    public function build(): string
    {
        $this->buildStatement();
        return parent::build();
    }

    public function update(null|string|Closure $table = null): static
    {
        $method = $this->isBulkQuery ? 'bulkUpdate' : 'update';

        list($table, $key) = $this->getUniqueTableName($method, $table ?? $this->getEntityManager()->table(), $this->queryMap);

        $this->table = $table;
        $this->updateMap['table'] = $table;
        $this->updateMap['method'] = $method;
        $this->queryMap[$key] = $method;
        $this->queryFlow[$method] = true;
        return $this;
    }

    public function bulkData(mixed $data): static
    {
        $this->updateMap[__FUNCTION__] = $data;
        $this->updateMap['method'] = __FUNCTION__;
        $this->queryFlow[__FUNCTION__] = true;
        $this->updateMap['from_source'] = 'subquery';
        return $this;
    }

    public function bulkUpdate(null|string|Closure $table = null, null|BulkUpdateType $type = null): static
    {
        $this->bulkType = $type;
        return $this->update($table);
    }

    public function set(mixed ...$data): static
    {
        $this->updateMap[__FUNCTION__] = $this->standardizer->setMethod(__FUNCTION__)->standardize($data);
        $this->queryFlow[__FUNCTION__] = true;
        return $this;
    }

    public function setColumn(string $column): static
    {
        $this->updateMap['setColumns'][] = $column;
        if (!isset($this->queryFlow[__FUNCTION__])) {
            $this->queryFlow[__FUNCTION__] = true;
        }
        return $this;
    }

    public function setColumns(string ...$columns): static
    {
        $this->updateMap[__FUNCTION__][] = $this->standardizer->setMethod(__FUNCTION__)->standardize($columns);
        $this->queryFlow[__FUNCTION__] = true;
        return $this;
    }

    public function setValue(mixed $value): static
    {
        $this->updateMap['setValues'][] = $value;
        if (!isset($this->queryFlow[__FUNCTION__])) {
            $this->queryFlow[__FUNCTION__] = true;
        }
        return $this;
    }

    public function setValues(mixed ...$values): static
    {
        $this->updateMap[__FUNCTION__][] = $this->standardizer->setMethod(__FUNCTION__)->standardize($values);
        $this->queryFlow[__FUNCTION__] = true;
        return $this;
    }

    public function innerJoin(mixed $query): static
    {
        if (is_object($query) && method_exists($query, 'setParent')) {
            $query->setParent($this);
        } else {
            $query = fn () => $query;
        }
        return $this->addJoin(__FUNCTION__, '', $query);
    }

    // public function where(mixed ...$conditions): static
    // {
    //     if (!isset($this->queryFlow['set'])) {
    //         $this->set();
    //     }
    //     $this->updateMap[__FUNCTION__][] = $this->standardizer->setMethod('where')->standardize($conditions);
    //     $this->queryFlow[__FUNCTION__] = true;
    //     return $this;
    // }

    public function getStatement(): SqlStatement
    {
        return self::TYPE;
    }

    /**
     * @return bool
     */
    public function isClosure(): bool
    {
        return $this->isClosure;
    }

    /**
     * @return array
     */
    public function getUpdateMap(): array
    {
        return $this->updateMap;
    }

    /**
     * @return array
     */
    public function getJoinMap(): array
    {
        return $this->joinMap;
    }

    /**
     * @return array
     */
    public function getOnMap(): array
    {
        return $this->onConditions;
    }

    public function assumeEntityManagerHasUpdateData(): void
    {
        if (!$this->em->hasData()) {
            throw new QueryFlowException('No data defined for update');
        }
        if (!isset($this->queryFlow['update']) && !isset($this->queryFlow['bulkUpdate'])) {
            $this->queryFlow['update'] = true;
            $this->queryFlow['set'] = true;
        }
    }

    public function hasUpdate(): bool
    {
        return isset($this->queryFlow['update']);
    }

    public function hasBulkUpdate(): bool
    {
        return isset($this->queryFlow['bulkUpdate']);
    }

    public function hasSet(): bool
    {
        return isset($this->queryFlow['set']);
    }

    public function hasWhere(): bool
    {
        return isset($this->queryFlow['where']);
    }

    public function assumeUpdateCurrentTable(): void
    {
        if (!$this->hasUpdate() && !$this->hasBulkUpdate()) {
            $this->update($this->resolveMainTable());
        }
    }

    public function getUpdateMapFragments(array $updateMap): array
    {
        $table = $this->getTableFromMap($updateMap);
        $setData = $this->getPayloadData($updateMap, 'set');
        $columnsData = $this->getPayloadData($updateMap, 'setColumns');
        $valuesData = $this->getPayloadData($updateMap, 'setValues');

        return [$table, $setData, $columnsData, $valuesData];
    }

    /**
     * @return null|BulkUpdateType
     */
    public function getBulkType(): ?BulkUpdateType
    {
        return $this->bulkType;
    }

    private function buildStatement(): void
    {
        $updateMap = $this->updateMap;

        if ($this->isBulkUpdate()) {
            $statement = new BulkUpdateStatement(
                $updateMap,
                $this->joinMap,
                $this->onConditions,
                $this->queryFlow,
                $this->em,
                $this->bulkType,
            );
        } else {
            $updateMap['where'] = $this->conditionsMap;
            $statement = new UpdateStatement(
                $updateMap,
                $this->queryFlow,
                $this->em,
            );
        }
        $this->add($statement);
    }

    private function getTableFromMap(array $updateMap): ?string
    {
        if (!isset($updateMap['table']) || ArrayUtils::isDeepEmpty($updateMap['table'])) {
            return null;
        }
        return $updateMap['table'];
    }

    private function getPayloadData(array $updateMap, string $key): ?array
    {
        if (!isset($updateMap[$key]) || !$updateMap[$key] instanceof SqlGenericDataPayload) {
            return null;
        }

        $data = $updateMap[$key]->getData();
        return empty($data) ? null : $data;
    }
}