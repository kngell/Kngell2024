<?php

declare(strict_types=1);

class QueryBuilderFactory
{
    protected QueryBuilderInterface $querybuider;

    /**
     * Main constructor
     * ============================================================.
     *@return void
     */
    public function __construct(QueryBuilderInterface $querybuider)
    {
        $this->querybuider = $querybuider;
    }

    /**
     * Create factory
     * =============================================================.
     *@return QueryBuilderInterface
     */
    public function create() : QueryBuilderInterface
    {
        if (!$this->querybuider instanceof QueryBuilderInterface) {
            throw new QueryBuilderExceptions($this->querybuider . ' is not a valid query builder Object!');
        }

        return $this->querybuider;
    }
}
