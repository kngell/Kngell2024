<?php

declare(strict_types=1);
class Update extends MainQuery
{
    public function __construct(TablesAliasHelper $tblh, string $table)
    {
        $this->tblh = $tblh;
        $this->table = $table;
    }

    public function getSql(): array
    {
        list($table, $alias) = $this->tblh->get($this->table, $this->tableAlias, $this->aliasCheck);
        return [
            $table . ' AS ' . $alias, //$statement . ' ' .
            $this->tableAlias,
            $this->aliasCheck,
            $this->parameters,
            $this->bind_arr,
        ];
    }
}