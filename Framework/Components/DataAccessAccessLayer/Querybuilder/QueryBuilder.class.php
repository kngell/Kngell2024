<?php

declare(strict_types=1);

class QueryBuilder extends AbstractQueryBuilder implements SqlCompositeQueryBuilderInterface
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    public function select(string|array|Closure ...$columns): SqlSelectQueryBuilderInterface
    {
        $query = (new SqlSelectQuery($this->em))->select($columns);
        $this->queryComponent = $query;
        $this->em->setQueryBuilder($this);
        return $query;
    }

    public function selectWithAlias(string|array|Closure ...$columns): SqlSelectQueryBuilderInterface
    {
        $query = new SqlSelectQuery($this->em);
        $query->withAlias()->select($columns);
        $this->queryComponent = $query;
        $this->em->setQueryBuilder($this);
        return $query;
    }

    public function with(string $cteTableName, SqlSelectQueryBuilderInterface|Closure $cteBody): SqlSelectQueryBuilderInterface
    {
        $query = (new SqlSelectQuery($this->em))->with($cteTableName, $cteBody);
        $this->queryComponent = $query;
        $this->em->setQueryBuilder($this);
        return $query;
    }

    public function withRecursive(string $cteTableName, SqlSelectQueryBuilderInterface|Closure $cteBody): SqlSelectQueryBuilderInterface
    {
        $query = (new SqlSelectQuery($this->em))->withRecursive($cteTableName, $cteBody);
        $this->queryComponent = $query;
        $this->em->setQueryBuilder($this);
        return $query;
    }

    public function update(null|string|Closure $table = null): SqlUpdateQueryBuilderInterface
    {
        $query = (new SqlUpdateQuery($this->em))->update($table);
        $this->queryComponent = $query;
        $this->em->setQueryBuilder($this);
        return $query;
    }

    public function insert(mixed ...$data): SqlInsertQueryBuilderInterface
    {
        $query = new SqlInsertQuery($this->em)->insert($data);
        $this->queryComponent = $query;
        $this->em->setQueryBuilder($this);
        return $query;
    }

    public function delete(string $table): SqlDeleteQueryBuilderInterface
    {
        $query = (new SqlDeleteQuery($this->em))->deleteFrom($table);
        $this->queryComponent = $query;
        return $query;
    }

    public function createTable(string $table): SqlDdlQueryBuilderInterface
    {
        $query = new SqlDdlQuery($this->em);
        $query->setMethod(SqlStatementType::CREATE->value);
        $this->queryComponent = $query;
        return $query;
    }

    public function getEntityManager(): EntityManagerInterface
    {
        return $this->em;
    }
}