<?php

declare(strict_types=1);
class WithDecorator extends AbstractCteDecorator
{
    private const SqlCteClause CLAUSE = SqlCteClause::WITH;

    public function build(): string
    {
        $query = $this->decoratedCte;
        $this->prepareChild($query);
        $sql = $query->build();
        $this->mergeChildState($query);
        return $sql;
    }

    public function getSqlClause(): null|SqlCteClause
    {
        return self::CLAUSE;
    }
}