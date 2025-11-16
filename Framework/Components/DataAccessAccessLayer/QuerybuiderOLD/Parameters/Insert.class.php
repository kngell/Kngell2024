<?php

declare(strict_types=1);
class Insert extends MainQuery
{
    public function __construct(EntityManagerInterface $em, string|null $table = null)
    {
        $this->table = $table;
        $this->em = $em;
    }

    public function getSql(): array
    {
        if ($this->em->isEntityKeyInitialized()) {
            throw new BadQueryArgumentException('The table key is auto incremented and cannot be set when inserting data');
        }
        if ($this->table === null) {
            throw new BadQueryArgumentException('You must define a table where to insert data');
        }
        return [
            $this->table,
            $this->tableAlias,
            $this->aliasCheck,
            $this->parameters,
            $this->bind_arr,
        ];
    }
}