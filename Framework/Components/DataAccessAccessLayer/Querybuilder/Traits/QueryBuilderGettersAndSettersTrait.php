<?php

declare(strict_types=1);

trait QueryBuilderGettersAndSettersTrait
{
    /**
     * @return SqlQueryComponent
     */
    public function getParent(): SqlQueryComponent
    {
        return $this->parent;
    }

    /**
     * @param SqlQueryComponent $parent
     *
     * @return SqlQueryComponent
     */
    public function setParent(?SqlQueryComponent $parent): SqlQueryComponent
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
     * @return SqlQueryComponent
     */
    public function setTableAlias(array $tableAlias): SqlQueryComponent
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
     * @return SqlQueryComponent
     */
    public function setAliasCheck(array $aliasCheck): SqlQueryComponent
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
     * @return SqlQueryComponent
     */
    public function setParameters(array $parameters): SqlQueryComponent
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
     * @return SqlQueryComponent
     */
    public function setBindArr(array $bindArr): SqlQueryComponent
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
     * @return SqlQueryComponent
     */
    public function setQuery(string $query): SqlQueryComponent
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
     * @return SqlQueryComponent
     */
    public function setLogicalToPhysicalMap(array $logicalToPhysicalMap): SqlQueryComponent
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
     * @return SqlQueryComponent
     */
    public function setMethod(string $method): SqlQueryComponent
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
     * @return SqlQueryComponent
     */
    public function setTables(array $tables): SqlQueryComponent
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
     * @return SqlQueryComponent
     */
    public function setJoinContext(?string $joinContext): SqlQueryComponent
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
     * @return SqlQueryComponent
     */
    public function setTable(string $table): SqlQueryComponent
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
     * @return SqlQueryComponent
     */
    public function setCustomAlias(?string $customAlias): SqlQueryComponent
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
     * @return SqlQueryComponent
     */
    public function setHelper(TablesAliasHelper $helper): SqlQueryComponent
    {
        $this->helper = $helper;

        return $this;
    }

    /**
     * @return ?ParameterManager
     */
    public function getParameterManager(): ?ParameterManager
    {
        if (isset($this->parameterManager)) {
            return $this->parameterManager;
        }

        return null;
    }

    /**
     * @param ParameterManager $parameterManager
     *
     * @return SqlQueryComponent
     */
    public function setParameterManager(ParameterManager $parameterManager): SqlQueryComponent
    {
        $this->parameterManager = $parameterManager;

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