<?php

declare(strict_types=1);

class JoinQueryStatement extends QueryStatement
{
    private string $joinType;

    public function __construct(QueryType $type, string $joinType, string $table)
    {
        parent::__construct($type);
        $this->joinType = $joinType;
        $this->table = $table;
    }

    public function getSql(): array
    {
        $tblh = $this->em->getTableAliasHelper();

        // Get the table alias
        list($table, $alias) = $tblh->get($this->table, $this->tableAlias, $this->aliasCheck);

        // Build the JOIN part
        $joinSql = $this->joinType . ' ' . $table . ' AS ' . $alias;

        // Use parent's getSql to process children (ON conditions)
        list($childrenSql, $childTableAlias, $childAliasCheck, $childParameters, $childBindArr) = parent::getSql();

        // Merge state from children
        $this->tableAlias = array_merge($this->tableAlias, $childTableAlias);
        $this->aliasCheck = array_merge($this->aliasCheck, $childAliasCheck);
        $this->parameters = array_merge($this->parameters, $childParameters);
        $this->bind_arr = array_merge($this->bind_arr, $childBindArr);

        // Add ON conditions if children produced any SQL
        if (!empty($childrenSql)) {
            $joinSql .= ' ON ' . $childrenSql;
        }

        $this->query = $joinSql;

        return [
            $this->query,
            $this->tableAlias,
            $this->aliasCheck,
            $this->parameters,
            $this->bind_arr,
        ];
    }

    public function getLogicalTable(): string
    {
        return $this->table;
    }
}