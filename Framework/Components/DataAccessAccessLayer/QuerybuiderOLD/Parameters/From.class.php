<?php

declare(strict_types=1);
class From extends MainQuery
{
    /**
     * @param EntityManagerInterface $em
     * @param QueryType $queryType
     * @param string $table
     * @return void
     */
    public function __construct(EntityManagerInterface $em, QueryType $queryType, string $table)
    {
        $this->em = $em;
        $this->table = $table;
        $this->queryType = $queryType;
    }

    public function getSql(): array
    {
        $tblh = $this->em->getTableAliasHelper();
        list($table, $alias) = $tblh->get($this->table, $this->tableAlias, $this->aliasCheck);
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