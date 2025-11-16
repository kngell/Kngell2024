<?php

declare(strict_types=1);
class QueryBuilderOLD
{
    private QueryStatement $select;
    private QueryStatement $from;
    private QueryStatement $where;
    private QueryStatement $join;
    private QueryStatement $on;
    private QueryStatement $groupBy;
    private QueryStatement $orderBy;
    private QueryStatement $limit;
    private QueryStatement $offset;
    private QueryStatement $having;
    private QueryStatement $update;
    private QueryStatement $set;
    private QueryStatement $insert;
    private QueryStatement $fields;
    private QueryStatement $values;
    private QueryStatement $delete;
    private QueryStatement $raw;
    private string $currentTableName;
    private string $joinMethod;
    private array $joinedMap = [];
    private string $whereMethod;
    private bool $selectAsAlias = false;
    private array $tables = [];
    private ?QueryType $queryType;
    private EntityManagerInterface $entityManager;
    private TypeNormalizerInterface $normalizer;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->currentTableName = $this->table($entityManager);
        $this->entityManager = $entityManager;
        $this->normalizer = $entityManager->getNormalizer();
    }

    public function raw(string $sql): self
    {
        !isset($this->queryType) ? $this->queryType = QueryType::get(__FUNCTION__) : '';
        !isset($this->raw) ? $this->raw = new QueryStatement($this->queryType) : '';
        $this->raw->add(new RawQuery($this->entityManager, $sql));
        $this->raw->getChildren()->last()->setMethod(__FUNCTION__);
        return $this;
    }

    public function select(array|string|null ...$columns): self
    {
        !isset($this->queryType) ? $this->queryType = QueryType::get(__FUNCTION__) : '';
        !isset($this->select) ? $this->select = new QueryStatement($this->queryType) : '';
        $this->select->add((new Select(
            $this->entityManager,
            $this->selectAsAlias,
            $columns,
        ))->setTable($this->currentTableName));
        $this->tables[$this->currentTableName] = $columns;
        $this->select->getChildren()->last()->setMethod(__FUNCTION__);
        return $this;
    }

    public function selectAsAlias(array|string|null ...$columns): self
    {
        $this->selectAsAlias = true;
        return $this->select($columns);
    }

    public function from(string|null $table = null): self
    {
        !isset($this->from) ? $this->from = new QueryStatement($this->queryType) : '';
        if (is_null($table)) {
            $table = $this->currentTableName;
        }
        if ($table !== $this->currentTableName && isset($this->select)) {
            $this->select->getChildren()->first()->setTable($table);
            $columns = $this->tables[$this->currentTableName];
            unset($this->tables[$this->currentTableName]);
            $this->tables[$table] = $columns;
            $this->currentTableName = $table;
        }
        $this->from->add(new From($this->entityManager, $this->queryType, $table));
        $this->from->getChildren()->last()->setMethod(__FUNCTION__);
        return $this;
    }

    public function where(mixed ...$conditions): self
    {
        $arrCheck = ArrayUtils::flattenArrayRecursive($conditions);
        if (!empty($arrCheck)) {
            !isset($this->where) ? $this->where = new QueryStatement($this->queryType) : '';
            $method = isset($this->whereMethod) ? $this->whereMethod : __FUNCTION__;
            $this->where->add(new Conditions(
                $this->entityManager,
                new self($this->entityManager),
                $this->normalizer,
                $this->tables,
                $conditions,
            ));
            $this->where->getChildren()->last()->setMethod($method);
        }
        return $this;
    }

    public function and(Closure $closure): self
    {
        return $this->addNestedCondition($closure, 'and');
    }

    public function or(Closure $closure): self
    {
        return $this->addNestedCondition($closure, 'or');
    }

    public function andWhere(mixed ...$conditions): self
    {
        $this->whereMethod = __FUNCTION__;
        $this->where($conditions);
        return $this;
    }

    public function orWhere(mixed ...$conditions): self
    {
        $this->whereMethod = __FUNCTION__;
        $this->where($conditions);
        return $this;
    }

    public function whereIn(string $column, array $conditions): self
    {
        $this->whereMethod = __FUNCTION__;
        $conditions = array_merge(['column' => $column], ['list' => $conditions]);
        $this->where($conditions);
        return $this;
    }

    public function whereNotIn(string $column, array $conditions): self
    {
        $this->whereMethod = __FUNCTION__;
        $conditions = array_merge(['column' => $column], ['list' => $conditions]);
        $this->where($conditions);
        return $this;
    }

    public function join(string $table, string|array|null ...$columns): self
    {
        !isset($this->join) ? $this->join = new QueryStatement($this->queryType) : '';

        $logicalKey = $table;
        $physicalTable = $table;

        if (array_key_exists($table, $this->tables)) {
            $counter = 1;
            do {
                $logicalKey = $table . '_join_' . $counter;
                $counter++;
            } while (array_key_exists($logicalKey, $this->tables));
        }

        $joinMethod = isset($this->joinMethod) ? $this->joinMethod : __FUNCTION__;
        $this->select->add((new Select(
            $this->entityManager,
            $this->selectAsAlias,
            $columns,
        ))->setTable($table)->setJoinContext($logicalKey));

        $joinComponent = new Join($this->entityManager, $table, $logicalKey, $this->queryType);
        $this->join->add($joinComponent);

        $this->tables[$logicalKey] = $columns;
        $joinComponent->setMethod($joinMethod);
        $this->select->getChildren()->last()->setMethod($joinMethod);

        return $this;
    }
    // public function join(string $table, string|array|null ...$columns): self
    // {
    //     !isset($this->join) ? $this->join = new QueryStatement($this->queryType) : '';

    //     $logicalKey = $table;
    //     $physicalTable = $table;

    //     if (array_key_exists($table, $this->tables)) {
    //         $counter = 1;
    //         do {
    //             $logicalKey = $table . '_join_' . $counter;
    //             $counter++;
    //         } while (array_key_exists($logicalKey, $this->tables));
    //     }

    //     $joinMethod = isset($this->joinMethod) ? $this->joinMethod : __FUNCTION__;

    //     $this->select->add((new Select(
    //         $this->entityManager,
    //         $this->selectAsAlias,
    //         $columns,
    //     ))->setTable($logicalKey));
    //     $this->join->add(new Join($this->entityManager, $logicalKey));

    //     $this->tables[$logicalKey] = $columns;
    //     $this->join->getChildren()->last()->setMethod($joinMethod);
    //     $this->select->getChildren()->last()->setMethod($joinMethod);

    //     return $this;
    // }

    public function innerJoin(string $table, string|array|null ...$columns): self
    {
        $this->joinMethod = __FUNCTION__;
        return $this->join($table, $columns);
    }

    public function leftJoin(string $table, string|array|null ...$columns): self
    {
        $this->joinMethod = __FUNCTION__;
        return $this->join($table, $columns);
    }

    public function rightJoin(string $table, string|array|null ...$columns): self
    {
        $this->joinMethod = __FUNCTION__;
        return $this->join($table, $columns);
    }

    public function on(array|string|int ...$conditions): self
    {
        if (!isset($this->join)) {
            throw new BadQueryRequestException('No joined table defined for On conditions');
        }

        // Get the last join to understand which table we're joining
        $lastJoin = $this->join->getChildren()->last();
        if (!$lastJoin instanceof Join) {
            throw new BadQueryRequestException('Invalid join structure');
        }

        // DEBUG: Check what we're setting

        // Create Conditions with the join context
        $conditionsObj = new Conditions(
            $this->entityManager,
            new self($this->entityManager),
            $this->normalizer,
            $this->tables,
            $conditions,
        );
        $this->joinedMap[$this->currentTableName] = $lastJoin->getLogicalTable();
        $conditionsObj->setMethod(__FUNCTION__);
        $conditionsObj->setJoinContext($lastJoin->getLogicalTable());
        $conditionsObj->setTable($lastJoin->getTable());

        // Add the conditions directly to the Join component
        $lastJoin->add($conditionsObj);

        return $this;
    }
    // public function on(array|string|int ...$conditions): self
    // {
    //     // ! isset($this->on) ? $this->on = new QueryStatement : '';
    //     $on = new QueryStatement($this->queryType);
    //     if (!isset($this->join)) {
    //         throw new BadQueryRequestException('No joined table defined for On conditions');
    //     }
    //     $method = __FUNCTION__;
    //     $on->add(new Conditions(
    //         $this->entityManager,
    //         new self($this->entityManager),
    //         $this->normalizer,
    //         $this->tables,
    //         $conditions,
    //     ));
    //     $on->getChildren()->last()->setMethod($method);
    //     $this->join->add($on);
    //     return $this;
    // }

    public function groupBy(string|array ...$columns): self
    {
        !isset($this->groupBy) ? $this->groupBy = new QueryStatement($this->queryType) : '';
        $this->groupBy->add((new GroupBy($this->entityManager, $this->tables, $columns)));
        $this->groupBy->getChildren()->last()->setMethod(__FUNCTION__);
        return $this;
    }

    public function having(mixed ...$conditions): self
    {
        !isset($this->having) ? $this->having = new QueryStatement($this->queryType) : '';
        $this->having->add(new Conditions(
            $this->entityManager,
            new self($this->entityManager),
            $this->normalizer,
            $this->tables,
            $conditions,
        ));
        $this->having->getChildren()->last()->setMethod(__FUNCTION__);
        return $this;
    }

    public function orderBy(string|array ...$orderBy): self
    {
        !isset($this->orderBy) ? $this->orderBy = new QueryStatement($this->queryType) : '';
        $this->orderBy->add(new OrderBy($this->entityManager, $this->tables, $orderBy));
        $this->orderBy->getChildren()->last()->setMethod(__FUNCTION__);
        return $this;
    }

    public function limit(int $limit): self
    {
        !isset($this->limit) ? $this->limit = new QueryStatement($this->queryType) : '';
        $this->limit->add(new LimitOffset($this->entityManager, $limit));
        $this->limit->getChildren()->last()->setMethod(__FUNCTION__);
        return $this;
    }

    public function offset(int $offset): self
    {
        !isset($this->offset) ? $this->offset = new QueryStatement($this->queryType) : '';
        $this->offset->add(new LimitOffset($this->entityManager, $offset));
        $this->offset->getChildren()->last()->setMethod(__FUNCTION__);
        return $this;
    }

    public function update(string|null $table = null): self
    {
        if ($table !== null) {
            $this->currentTableName = $table;
        }
        !isset($this->queryType) ? $this->queryType = QueryType::get(__FUNCTION__) : '';
        !isset($this->update) ? $this->update = new QueryStatement($this->queryType) : '';
        $this->update->add(new Update($this->entityManager, $this->currentTableName));
        $this->update->getChildren()->last()->setMethod(__FUNCTION__);
        return $this;
    }

    public function set(mixed ...$keyValues): self
    {
        !isset($this->set) ? $this->set = new QueryStatement($this->queryType) : '';
        $this->set->add(new Conditions(
            $this->entityManager,
            new self($this->entityManager),
            $this->normalizer,
            $this->tables,
            $keyValues,
        ));
        $this->set->getChildren()->last()->setMethod(__FUNCTION__);
        return $this;
    }

    public function insert(string|Entity|null $table = null): self
    {
        $this->checkTable($table);
        !isset($this->queryType) ? $this->queryType = QueryType::get(__FUNCTION__) : '';
        !isset($this->insert) ? $this->insert = new QueryStatement($this->queryType) : '';
        $this->insert->add(new Insert($this->entityManager, $this->currentTableName));
        $this->insert->getChildren()->last()->setMethod(__FUNCTION__);
        return $this;
    }

    public function into(string|null $table = null): self
    {
        if ($table !== null) {
            $this->currentTableName = $table;
        }
        if (!isset($this->insert)) {
            return $this->insert($table);
        }
        $this->insert->getChildren()->last()->setTable($table);
        return $this;
    }

    public function fields(array|string|null ...$columns): self
    {
        !isset($this->fields) ? $this->fields = new QueryStatement($this->queryType) : '';
        $this->fields->add(new Fields($this->entityManager, $columns));
        $this->fields->getChildren()->last()->setMethod(__FUNCTION__);
        return $this;
    }

    public function values(array|string|null|int ...$values): self
    {
        !isset($this->values) ? $this->values = new QueryStatement($this->queryType) : '';
        $this->values->add(new Conditions(
            $this->entityManager,
            new self($this->entityManager),
            $this->normalizer,
            $values,
        ));
        $this->values->getChildren()->last()->setMethod(__FUNCTION__);
        return $this;
    }

    public function delete(string|null $table = null): self
    {
        if ($table !== null) {
            $this->currentTableName = $table;
        }
        !isset($this->queryType) ? $this->queryType = QueryType::get(__FUNCTION__) : '';
        !isset($this->delete) ? $this->delete = new QueryStatement($this->queryType) : '';
        $this->delete->add(new Delete($this->currentTableName));
        $this->delete->getChildren()->last()->setMethod(__FUNCTION__);
        return $this;
    }

    /**
     * Get the value of where.
     *
     * @return QueryStatement
     */
    public function getWhere(): QueryStatement
    {
        return $this->where;
    }

    /**
     * Set the value of tables.
     *
     * @param array $tables
     *
     * @return self
     */
    public function setTables(array $tables): self
    {
        $this->tables = $tables;
        return $this;
    }

    public function build(): MainQuery
    {
        $flows = $this->queryType->getFlow();
        $query = new QueryStatement($this->queryType);
        foreach ($flows as $statement => $required) {
            if (!isset($this->{$statement}) && $required) {
                $this->$statement();
            }
            if (isset($this->{$statement})) {
                $query->add($this->{$statement});
            }
        }
        $query->getSql();
        $this->entityManager->setQueryExpr($query);
        return $query;
    }

    /**
     * Get the value of currentTableName.
     *
     * @return string
     */
    public function getCurrentTableName(): string
    {
        return $this->currentTableName;
    }

    /**
     * Get the value of entityManager.
     *
     * @return EntityManagerInterface
     */
    public function getEntityManager(): EntityManagerInterface
    {
        return $this->entityManager;
    }

    /**
     * Set the QueryType for this builder (used for nested builders).
     */
    public function setQueryType(?QueryType $queryType): void
    {
        $this->queryType = $queryType;
    }

    private function table(EntityManagerInterface $entityManager): string
    {
        $table = $entityManager->table();
        return preg_replace('/_(show|view|display|preview|detail)$/i', '', $table);
    }

    /**
     * Handle nested conditions with closures.
     */
    private function addNestedCondition(Closure $closure, string $operator): self
    {
        !isset($this->where) ? $this->where = new QueryStatement($this->queryType) : '';

        // Create a nested query builder for the closure
        $nestedBuilder = new self($this->entityManager);
        $nestedBuilder->setQueryType($this->queryType);
        $closure($nestedBuilder);

        // Get the nested WHERE conditions
        $nestedWhere = $nestedBuilder->getWhere();

        if ($nestedWhere->getChildren()->count() > 0) {
            // Mark this as a nested condition group
            $nestedWhere->setNestedOperator($operator);
            $nestedWhere->setMethod('where');
            $this->where->add($nestedWhere);
        }

        return $this;
    }

    private function checkTable(string|Entity|null $table): void
    {
        if (is_string($table)) {
            $this->currentTableName = $table;
        } elseif ($table instanceof Entity) {
            $this->entityManager->setEntity($table);
            $this->currentTableName = $this->entityManager->table();
        }
    }
}