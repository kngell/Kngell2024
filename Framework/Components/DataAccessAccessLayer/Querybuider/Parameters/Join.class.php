<?php

declare(strict_types=1);
class Join extends MainQuery
{
    public function __construct(TablesAliasHelper $tblh, string $table)
    {
        $this->tblh = $tblh;
        $this->table = $table;
    }

    public function getSql(): array
    {
        $statement = strtoupper(Statement::from($this->method)->value);
        list($table, $alias) = $this->tblh->get($this->table, $this->tableAlias, $this->aliasCheck);
        return [
            $statement . ' ' . $table . ' AS ' . $alias,
            $this->tableAlias,
            $this->aliasCheck,
            $this->parameters,
            $this->bind_arr,
        ];
    }
}