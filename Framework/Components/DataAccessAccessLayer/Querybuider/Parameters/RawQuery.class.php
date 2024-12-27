<?php

declare(strict_types=1);

class RawQuery extends MainQuery
{
    public function __construct(private EntityManagerInterface $en, private string $sql)
    {
    }

    public function getSql(): array
    {
        return [
            $this->sql,
            $this->tableAlias,
            $this->aliasCheck,
            $this->parameters,
            $this->bind_arr,
        ];
    }
}