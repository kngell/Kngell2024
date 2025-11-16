<?php

declare(strict_types=1);

class SelectSatement extends SqlQuery
{
    public function __construct(private SqlClause $clause)
    {
        parent::__construct();
    }

    public function build(): string
    {
        $result = [];
        $result[] = $this->clause->name;
        $result[] = parent::build();
        return implode(' ', $result);
    }
}