<?php

declare(strict_types=1);

class QueryBuilder extends AbstractQueryBuilder implements SqlCompositeQueryBuilderInterface
{
    public function __construct(private EntityManagerInterface $em)
    {
        parent::__construct($em);
    }

    public function select(string|array|Closure ...$columns): SqlSelectQueryBuilderInterface
    {
        $query = (new SqlSelectQuery($this->em, $this->isBulkQuery))
        ->select($columns);
        $lastComponent = end($this->executedComponents);
        if ($lastComponent) {
            $query->setQueryMap($lastComponent->getQueryMap());
        }
        $this->registerComponent($query, __FUNCTION__);
        $this->em->setQueryBuilder($this);
        return $query;
    }

    public function selectWithAlias(string|array|Closure ...$columns): SqlSelectQueryBuilderInterface
    {
        $query = new SqlSelectQuery($this->em, $this->isBulkQuery);
        $query->withAlias()->select($columns);
        $this->registerComponent($query, __FUNCTION__);
        $this->em->setQueryBuilder($this);
        return $query;
    }

    public function selectDistinct(string|array|Closure ...$columns): SqlSelectQueryBuilderInterface
    {
        $query = new SqlSelectQuery($this->em, $this->isBulkQuery);
        $query->distinct()->select($columns);
        $this->registerComponent($query, __FUNCTION__);
        $this->em->setQueryBuilder($this);
        return $query;
    }

    public function selectDistinctCount(string|array|Closure ...$columns): SqlSelectQueryBuilderInterface
    {
        $query = new SqlSelectQuery($this->em, $this->isBulkQuery);
        $query->distinctCount()->select($columns);
        $this->registerComponent($query, __FUNCTION__);
        $this->em->setQueryBuilder($this);
        return $query;
    }

    public function selectDistinctWithAliases(string|array|Closure ...$columns): SqlSelectQueryBuilderInterface
    {
        $query = new SqlSelectQuery($this->em, $this->isBulkQuery);
        $query->withAlias()->distinct()->select($columns);
        $this->registerComponent($query, __FUNCTION__);
        $this->em->setQueryBuilder($this);
        return $query;
    }

    public function with(string $cteTableName): SqlCteSelectQueryBuilderInterface
    {
        return $this->cteQuery($cteTableName, __FUNCTION__);
    }

    public function case(mixed ...$conditions): CaseExpressionBuilderInterface
    {
        $lastComponent = end($this->executedComponents);
        $currentStatement = $lastComponent?->getStatement() ?? $this->statementContext;
        $query = (new CaseExpressionCollector($this->em, $currentStatement))->case($conditions);

        if ($lastComponent) {
            $query->setQueryMap($lastComponent->getQueryMap());
        }

        $this->registerComponent($query, __FUNCTION__);
        $this->em->setQueryBuilder($this);
        return $query;
    }

    public function when(mixed ...$conditions): CaseExpressionBuilderInterface
    {
        $currentStatement = $this->statementContext;
        $lastComponent = end($this->executedComponents);
        if ($lastComponent) {
            $currentStatement = $lastComponent?->getStatement() ?? $this->statementContext;
        }

        $query = (new CaseExpressionCollector($this->em, $currentStatement))->when($conditions);

        if ($lastComponent) {
            $query->setQueryMap($lastComponent->getQueryMap());
        }

        $this->registerComponent($query, __FUNCTION__);
        $this->em->setQueryBuilder($this);
        return $query;
    }

    public function withRecursive(string $cteTableName): SqlCteSelectQueryBuilderInterface
    {
        return $this->cteQuery($cteTableName, __FUNCTION__);
    }

    public function update(null|string|Closure $table = null): SqlUpdateQueryBuilderInterface
    {
        $query = (new SqlUpdateQuery($this->em, $this->isBulkQuery))->update($table);
        $this->registerComponent($query, __FUNCTION__);
        $this->em->setQueryBuilder($this);
        return $query;
    }

    public function bulkUpdate(null|string|Closure $table = null, null|BulkUpdateType $type = null): SqlUpdateQueryBuilderInterface
    {
        $this->isBulkQuery = true;
        $query = (new SqlUpdateQuery($this->em, $this->isBulkQuery))->bulkUpdate($table, $type);
        $this->registerComponent($query, __FUNCTION__);
        $this->em->setQueryBuilder($this);
        return $query;
    }

    public function insert(mixed ...$data): SqlInsertQueryBuilderInterface
    {
        $query = new SqlInsertQuery($this->em)->insert($data);
        $this->registerComponent($query, __FUNCTION__);
        $this->em->setQueryBuilder($this);
        return $query;
    }

    public function delete(null|string|Closure $table = null, null|string $alias = null): SqlDeleteQueryBuilderInterface
    {
        $query = (new SqlDeleteQuery($this->em))->deleteFrom($table);
        $this->registerComponent($query, __FUNCTION__);
        $this->em->setQueryBuilder($this);
        return $query;
    }

    public function createTable(string $table): SqlDdlQueryBuilderInterface
    {
        $query = new SqlDdlQuery($this->em);
        $query->setMethod(SqlStatement::CREATE->value);
        $this->registerComponent($query, __FUNCTION__);
        return $query;
    }

    /**
     * @param null|SqlStatement $statementContext
     *
     * @return QueryBuilder
     */
    public function setStatementContext(?SqlStatement $statementContext): QueryBuilder
    {
        $this->statementContext = $statementContext;

        return $this;
    }

    private function cteQuery(string $cteTableName, string $method): SqlCteSelectQueryBuilderInterface
    {
        $query = (new SqlCteSelectQuery($this->em));

        $lastComponent = end($this->executedComponents);
        if ($lastComponent) {
            $query->setQueryMap($lastComponent->getQueryMap());
        }

        $query->$method($cteTableName);

        $this->registerComponent($query, __FUNCTION__);
        $this->em->setQueryBuilder($this);
        return $query;
    }
}