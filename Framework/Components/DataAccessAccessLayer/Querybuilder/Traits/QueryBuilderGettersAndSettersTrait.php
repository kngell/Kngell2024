<?php

declare(strict_types=1);

trait QueryBuilderGettersAndSettersTrait
{
    /**
     * @return SqlComponent
     */
    public function getParent(): SqlComponent
    {
        return $this->parent;
    }

    /**
     * @param SqlComponent $parent
     *
     * @return SqlComponent
     */
    public function setParent(?SqlComponent $parent): SqlComponent
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * @return array
     */
    public function getTableAlias(): array
    {
        return $this->tableAlias;
    }

    /**
     * @param array $tableAlias
     *
     * @return SqlComponent
     */
    public function setTableAlias(array $tableAlias): SqlComponent
    {
        $this->tableAlias = $tableAlias;

        return $this;
    }

    /**
     * @return array
     */
    public function getAliasCheck(): array
    {
        return $this->aliasCheck;
    }

    /**
     * @param array $aliasCheck
     *
     * @return SqlComponent
     */
    public function setAliasCheck(array $aliasCheck): SqlComponent
    {
        $this->aliasCheck = $aliasCheck;

        return $this;
    }

    /**
     * @return array
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * @param array $parameters
     *
     * @return SqlComponent
     */
    public function setParameters(array $parameters): SqlComponent
    {
        $this->parameters = $parameters;

        return $this;
    }

    /**
     * @return array
     */
    public function getBindArr(): array
    {
        return $this->bindArr;
    }

    /**
     * @param array $bindArr
     *
     * @return SqlComponent
     */
    public function setBindArr(array $bindArr): SqlComponent
    {
        $this->bindArr = $bindArr;

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

    public function getLogicalToPhysicalMap(): array
    {
        return $this->logicalToPhysicalMap;
    }

    /**
     * @param array $logicalToPhysicalMap
     *
     * @return SqlComponent
     */
    public function setLogicalToPhysicalMap(array $logicalToPhysicalMap): SqlComponent
    {
        $this->logicalToPhysicalMap = $logicalToPhysicalMap;

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
     * @return array
     */
    public function getTables(): array
    {
        return $this->tables;
    }

    /**
     * @param array $tables
     *
     * @return SqlComponent
     */
    public function setTables(array $tables): SqlComponent
    {
        $this->tables = $tables;

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
     * @return array
     */
    public function getParameterManagerArray(): array
    {
        return $this->parameterManagerArray;
    }

    /**
     * @return CollectionInterface
     */
    public function getChildren(): CollectionInterface
    {
        return $this->children;
    }
}