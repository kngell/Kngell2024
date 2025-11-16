<?php

declare(strict_types=1);

class Join extends QueryStatement
{
    public function __construct(EntityManagerInterface $em, string $table, string $joinContext, QueryType $type)
    {
        $this->em = $em;
        $this->table = $table;
        $this->joinContext = $joinContext;
        parent::__construct($type);
    }

    public function getSql(): array
    {
        $tblh = $this->em->getTableAliasHelper();
        $statement = strtoupper(Statement::from($this->method)->name);
        $statement = str_replace('_', ' ', $statement);

        list($table, $alias) = $tblh->get($this->joinContext, $this->tableAlias, $this->aliasCheck);
        $joinSql = [];
        // Build the JOIN part
        $joinSql[] = $statement . ' ' . $table . ' AS ' . $alias;

        // Use parent's getSql to process children (ON conditions)
        list($childrenSql, $childTableAlias, $childAliasCheck, $childParameters, $childBindArr) = parent::getSql();

        // Merge state from children
        $this->tableAlias = $this->safeArrayMerge($this->tableAlias, $childTableAlias ?? []);
        $this->aliasCheck = $this->safeArrayMerge($this->aliasCheck, $this->addAliasCheck($childAliasCheck ?? []));
        $this->parameters = $this->safeArrayMerge($this->parameters, $childParameters ?? []);
        $this->bind_arr = $this->safeArrayMerge($this->bind_arr, $childBindArr ?? []);

        // Add ON conditions if children produced any SQL
        if (!empty($childrenSql)) {
            $joinSql[] = $childrenSql;
        }

        $this->query = implode(' ', $joinSql);

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
        return $this->joinContext;
    }
}