<?php

declare(strict_types=1);

trait QueryBuilderGettersAndSettersTrait
{
    /**
     * @return ?SqlComponent
     */
    public function getParent(): ?SqlComponent
    {
        return $this->parent;
    }

    /**
     * @param null|SqlComponent $parent
     *
     * @return SqlComponent
     */
    public function setParent(?SqlComponent $parent): SqlComponent
    {
        $this->parent = $parent;
        return $this;
    }

    /**
     * @return string
     */
    public function getQuery(): string
    {
        return $this->query;
    }

    /**
     * @param string $query
     *
     * @return SqlComponent
     */
    public function setQuery(string $query): SqlComponent
    {
        $this->query = $query;

        return $this;
    }

    /**
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * @param string $method
     *
     * @return SqlComponent
     */
    public function setMethod(string $method): SqlComponent
    {
        $this->method = $method;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getJoinContext(): ?string
    {
        return $this->joinContext;
    }

    /**
     * @param null|string $joinContext
     *
     * @return SqlComponent
     */
    public function setJoinContext(?string $joinContext): SqlComponent
    {
        $this->state->joinContext = $joinContext;
        $this->joinContext = $joinContext;

        return $this;
    }

    public function setContext(?StatementType $context): ?SqlComponent
    {
        $this->context = $context;
        return $this;
    }

    /**
     * @return null|StatementType
     */
    public function getContext(): ?StatementType
    {
        return $this->context;
    }

    /**
     * @return null|string
     */
    public function getTable(): ?string
    {
        return $this->table;
    }

    /**
     * @param string $table
     *
     * @return SqlComponent
     */
    public function setTable(string $table): SqlComponent
    {
        $this->table = $table;
        return $this;
    }

    /**
     * @return string
     */
    public function getCustomAlias(): string
    {
        return $this->customAlias;
    }

    /**
     * @param string $customAlias
     *
     * @return SqlComponent
     */
    public function setCustomAlias(?string $customAlias): SqlComponent
    {
        $this->customAlias = $customAlias;

        return $this;
    }

    /**
     * @return TablesAliasHelper
     */
    public function getHelper(): TablesAliasHelper
    {
        return $this->helper;
    }

    /**
     * @param TablesAliasHelper $helper
     *
     * @return SqlComponent
     */
    public function setHelper(TablesAliasHelper $helper): SqlComponent
    {
        $this->helper = $helper;

        return $this;
    }

    /**
     * @return CollectionInterface
     */
    public function getChildren(): CollectionInterface
    {
        return $this->children;
    }

    /**
     * @param null|SqlStatement $sqlStatement
     *
     * @return SqlComponent
     */
    public function setStatement(?SqlStatement $sqlStatement): SqlComponent
    {
        $this->sqlStatement = $sqlStatement;

        return $this;
    }

    /**
     * @return null|SqlStatement
     */
    public function getStatement(): ?SqlStatement
    {
        return $this->sqlStatement;
    }
}