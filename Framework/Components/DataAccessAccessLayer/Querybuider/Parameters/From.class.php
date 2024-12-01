<?php

declare(strict_types=1);
class From extends MainQuery
{
    /**
     * @param TablesAliasHelper $tblh
     * @param QueryType $queryType
     * @param string $table
     * @return void
     */
    public function __construct(TablesAliasHelper $tblh, QueryType $queryType, string $table)
    {
        $this->tblh = $tblh;
        $this->table = $table;
        $this->queryType = $queryType;
    }

    public function getSql(): array
    {
        list($table, $alias) = $this->tblh->get($this->table, $this->tableAlias, $this->aliasCheck);
        $query = $table . ' AS ' . $alias;
        if ($this->queryType->value === 'delete') {
            $query = $table;
        }
        return [
            $query,
            $this->tableAlias,
            $this->aliasCheck,
            $this->parameters,
            $this->bind_arr,
        ];
    }
}