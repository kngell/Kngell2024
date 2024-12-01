<?php

declare(strict_types=1);
class QueryBuilder
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
    private string $currentTableName;
    private string $joinMethod;
    private string $whereMethod;
    private array $tables = [];
    private ?TablesAliasHelper $tblh;
    private ?QueryType $queryType;
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->currentTableName = $entityManager->table();
        $this->tblh = $entityManager->getTableAliasHelper();
        $this->entityManager = $entityManager;
    }

    public function select(array|string|null ...$columns): self
    {
        ! isset($this->queryType) ? $this->queryType = QueryType::get(__FUNCTION__) : '';
        ! isset($this->select) ? $this->select = new QueryStatement : '';
        $this->select->add((new Select($this->tblh, $columns))->setTable($this->currentTableName));
        $this->tables[$this->currentTableName] = $columns;
        $this->select->getChildren()->last()->setMethod(__FUNCTION__);
        return $this;
    }

    public function from(string|null $table = null): self
    {
        ! isset($this->from) ? $this->from = new QueryStatement : '';
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
        $this->from->add(new From($this->tblh, $this->queryType, $table));
        $this->from->getChildren()->last()->setMethod(__FUNCTION__);
        return $this;
    }

    public function where(mixed ...$conditions): self
    {
        ! isset($this->where) ? $this->where = new QueryStatement : '';
        // if (! isset($this->from)) {
        //     $this->from($this->currentTableName);
        // }
        $method = isset($this->whereMethod) ? $this->whereMethod : __FUNCTION__;
        $this->where->add(new Conditions($this->tblh, new self($this->entityManager, $this->tblh), $this->tables, $conditions));
        $this->where->getChildren()->last()->setMethod($method);
        return $this;
    }

    public function and(mixed ...$conditions) : self
    {
        $this->whereMethod = __FUNCTION__;
        $this->where($conditions);
        return $this;
    }

    public function andWhere(mixed ...$conditions) : self
    {
        $this->whereMethod = __FUNCTION__;
        $this->where($conditions);
        return $this;
    }

    public function orWhere(mixed ...$conditions) : self
    {
        $this->whereMethod = __FUNCTION__;
        $this->where($conditions);
        return $this;
    }

    public function whereIn(string $column, array $conditions) : self
    {
        $this->whereMethod = __FUNCTION__;
        $conditions = array_merge(['column' => $column], ['list' => $conditions]);
        $this->where($conditions);
        return $this;
    }

    public function whereNotIn(string $column, array $conditions) : self
    {
        $this->whereMethod = __FUNCTION__;
        $conditions = array_merge(['column' => $column], ['list' => $conditions]);
        $this->where($conditions);
        return $this;
    }

    public function join(string $table, string|array|null ...$columns): self
    {
        ! isset($this->join) ? $this->join = new QueryStatement : '';
        if (array_key_exists($table, array_keys($this->tables))) {
            throw new BadQueryRequestException("$table already exist in this request. Please join another table");
        }
        $joinMethod = isset($this->joinMethod) ? $this->joinMethod : __FUNCTION__;
        $this->select->add((new Select($this->tblh, $columns))->setTable($table));
        $this->join->add(new Join($this->tblh, $table));
        $this->tables[$table] = $columns;
        $this->join->getChildren()->last()->setMethod($joinMethod);
        $this->select->getChildren()->last()->setMethod($joinMethod);
        return $this;
    }

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

    public function on(array|string ...$conditions): self
    {
        ! isset($this->on) ? $this->on = new QueryStatement : '';
        if (! isset($this->join)) {
            throw new BadQueryRequestException('No joined table defined for On conditions');
        }
        $method = __FUNCTION__;
        $this->on->add(new Conditions($this->tblh, new self($this->entityManager, $this->tblh), $this->tables, $conditions));
        $this->on->getChildren()->last()->setMethod($method);
        return $this;
    }

    public function groupBy(string|array ...$columns): self
    {
        ! isset($this->groupBy) ? $this->groupBy = new QueryStatement : '';
        $this->groupBy->add((new GroupBy($this->tblh, $this->tables, $columns)));
        $this->groupBy->getChildren()->last()->setMethod(__FUNCTION__);
        return $this;
    }

    public function having(mixed ...$conditions): self
    {
        ! isset($this->having) ? $this->having = new QueryStatement : '';
        $this->having->add(new Conditions($this->tblh, new self($this->entityManager, $this->tblh), $this->tables, $conditions));
        $this->having->getChildren()->last()->setMethod(__FUNCTION__);
        return $this;
    }

    public function orderBy(string|array ...$orderBy): self
    {
        ! isset($this->orderBy) ? $this->orderBy = new QueryStatement : '';
        $this->orderBy->add(new OrderBy($this->tblh, $this->tables, $orderBy));
        $this->orderBy->getChildren()->last()->setMethod(__FUNCTION__);
        return $this;
    }

    public function limit(int $limit): self
    {
        ! isset($this->limit) ? $this->limit = new QueryStatement : '';
        $this->limit->add(new LimitOffset($this->tblh, $limit));
        $this->limit->getChildren()->last()->setMethod(__FUNCTION__);
        return $this;
    }

    public function offset(int $offset): self
    {
        ! isset($this->offset) ? $this->offset = new QueryStatement : '';
        $this->offset->add(new LimitOffset($this->tblh, $offset));
        $this->offset->getChildren()->last()->setMethod(__FUNCTION__);
        return $this;
    }

    public function update(string|null $table = null) : self
    {
        if ($table !== null) {
            $this->currentTableName = $table;
        }
        ! isset($this->queryType) ? $this->queryType = QueryType::get(__FUNCTION__) : '';
        ! isset($this->update) ? $this->update = new QueryStatement : '';
        $this->update->add(new Update($this->tblh, $this->currentTableName));
        $this->update->getChildren()->last()->setMethod(__FUNCTION__);
        return $this;
    }

    public function set(mixed ...$keyValues) : self
    {
        ! isset($this->set) ? $this->set = new QueryStatement : '';
        $this->set->add(new Conditions($this->tblh, new self($this->entityManager, $this->tblh), [], $keyValues));
        $this->set->getChildren()->last()->setMethod(__FUNCTION__);
        return $this;
    }

    public function insert(string|null $table = null) : self
    {
        if ($table !== null) {
            $this->currentTableName = $table;
        }
        ! isset($this->queryType) ? $this->queryType = QueryType::get(__FUNCTION__) : '';
        ! isset($this->insert) ? $this->insert = new QueryStatement : '';
        $this->insert->add(new Insert($this->currentTableName));
        $this->insert->getChildren()->last()->setMethod(__FUNCTION__);
        return $this;
    }

    public function into(string|null $table = null) : self
    {
        if ($table !== null) {
            $this->currentTableName = $table;
        }
        if (! isset($this->insert)) {
            return $this->insert($table);
        }
        $this->insert->getChildren()->last()->setTable($table);
        return $this;
    }

    public function fields(array|string|null ...$columns) : self
    {
        ! isset($this->fields) ? $this->fields = new QueryStatement : '';
        $this->fields->add(new Fields($columns));
        $this->fields->getChildren()->last()->setMethod(__FUNCTION__);
        return $this;
    }

    public function values(array|string|null|int ...$values) : self
    {
        ! isset($this->values) ? $this->values = new QueryStatement : '';
        $this->values->add(new Fields($values));
        $this->values->getChildren()->last()->setMethod(__FUNCTION__);
        return $this;
    }

    public function delete(string|null $table = null) : self
    {
        if ($table !== null) {
            $this->currentTableName = $table;
        }
        ! isset($this->queryType) ? $this->queryType = QueryType::get(__FUNCTION__) : '';
        ! isset($this->delete) ? $this->delete = new QueryStatement : '';
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
        $query = new QueryStatement();
        foreach ($flows as $statement => $required) {
            if (! isset($this->{$statement}) && $required) {
                $this->$statement();
            }
            if (isset($this->{$statement})) {
                $query->add($this->{$statement});
            }
        }
        $results = $query->getSql();
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
}