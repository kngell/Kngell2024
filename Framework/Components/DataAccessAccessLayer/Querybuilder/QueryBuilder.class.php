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
        $query->setMethod(SqlStatementType::SELECT->value);
        $this->queryComponent = $query;
        $this->em->setQueryBuilder($this);
        return $query;
    }

    public function update(string|array|Closure ...$columns): SqlUpdateQueryBuilderInterface
    {
        $query = new SqlUpdateQuery($columns, $this->em);
        $query->setMethod(SqlStatementType::UPDATE->value);
        $this->queryComponent = $query;
        return $query;
    }

    public function insert(string|array|Closure ...$columns): SqlInsertQueryBuilderInterface
    {
        $query = new SqlInsertQuery($this->em)->insert($columns);
        $this->queryComponent = $query;
        $this->em->setQueryBuilder($this);
        return $query;
    }

    public function delete(string|array|Closure ...$columns): SqlDeleteQueryBuilderInterface
    {
        $query = new SqlDeleteQuery($columns, $this->em);
        $query->setMethod(SqlStatementType::DELETE->value);
        $this->queryComponent = $query;
        return $query;
    }

    public function createTable(string $table): SqlDdlQueryBuilderInterface
    {
        $query = new SqlDdlQuery($table, $this->em);
        $query->setMethod(SqlStatementType::CREATE->value);
        $this->queryComponent = $query;
        return $query;
    }

    public function selectWithAlias(string|array|Closure ...$columns): SqlSelectQueryBuilderInterface
    {
        return $this->select(...$columns)->withAlias();
    }

    public function getEntityManager(): EntityManagerInterface
    {
        return $this->em;
    }
}