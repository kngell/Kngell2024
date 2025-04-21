<?php

declare(strict_types=1);
class Update extends MainQuery
{
    public function __construct(EntityManagerInterface $em, string $table)
    {
        $this->em = $em;
        $this->table = $table;
    }

    // UPDATE Q
    // SET Q.TITLE = 'TEST'
    // FROM HOLD_TABLE Q
    // WHERE Q.ID = 101;
    public function getSql(): array
    {
        $tblh = $this->em->getTableAliasHelper();
        list($table, $alias) = $tblh->get($this->table, $this->tableAlias, $this->aliasCheck);
        return [
            $table, // $table . ' AS ' . $statement . ' ' .
            $this->tableAlias,
            $this->aliasCheck,
            $this->parameters,
            $this->bind_arr,
        ];
    }
}
