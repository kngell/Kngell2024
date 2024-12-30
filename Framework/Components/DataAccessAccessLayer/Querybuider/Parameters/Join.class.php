<?php

declare(strict_types=1);
class Join extends MainQuery
{
    public function __construct(EntityManagerInterface $em, string $table)
    {
        $this->em = $em;
        $this->table = $table;
    }

    public function getSql(): array
    {
        $tblh = $this->em->getTableAliasHelper();
        $statement = strtoupper(Statement::from($this->method)->name);
        $statement = str_replace('_', ' ', $statement);
        list($table, $alias) = $tblh->get($this->table, $this->tableAlias, $this->aliasCheck);
        return [
            $statement . ' ' . $table . ' AS ' . $alias,
            $this->tableAlias,
            $this->aliasCheck,
            $this->parameters,
            $this->bind_arr,
        ];
    }
}
