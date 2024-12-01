<?php

declare(strict_types=1);
class Insert extends MainQuery
{
    public function __construct(string|null $table = null)
    {
        $this->table = $table;
    }

    public function getSql(): array
    {
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