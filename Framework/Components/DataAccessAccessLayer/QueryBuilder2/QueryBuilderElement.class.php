<?php

declare(strict_types=1);
class QueryBuilderElement extends AbstractQueryBuilderElement
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function select(array|string|null ...$columns): QuerySelectElement
    {
        return new QuerySelectElement($columns, QueryType::get(__FUNCTION__), $this->entityManager);
    }

    public function update(string|null $table = null) : QueryUpdateElement
    {
        return new QueryUpdateElement($table, QueryType::get(__FUNCTION__), $this->entityManager);
    }

    public function insert(string|Entity|null $table = null) : QueryInsert
    {
        return new QueryInsert($table, QueryType::get(__FUNCTION__), $this->entityManager);
    }

    public function delete(string|null $table = null) : QueryDelete
    {
        return new QueryDelete($table, QueryType::get(__FUNCTION__), $this->entityManager);
    }

    public function build(): AbstractQueryBuilderElement
    {
    }
}
