<?php

declare(strict_types=1);
class WithRecursiveDecorator extends AbstractCteDecorator
{
    private const SqlCteClause CLAUSE = SqlCteClause::WITH_RECURSIVE;

    public function build(): string
    {
        $this->prepareChild($this->decoratedCte);
        $this->query = $this->decoratedCte->build();
        $this->mergeChildState($this->decoratedCte);
        $this->decoratedCte->state->joinContext = null;
        return $this->query;
    }

    public function getSqlClause(): null|SqlCteClause
    {
        return self::CLAUSE;
    }
}