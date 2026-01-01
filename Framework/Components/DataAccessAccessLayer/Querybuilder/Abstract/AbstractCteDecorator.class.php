<?php

declare(strict_types=1);
abstract class AbstractCteDecorator extends SqlComponent implements ClauseComponentInterface
{
    protected WithClause $decoratedCte;

    public function __construct(WithClause $decoratedCte)
    {
        parent::__construct();
        $this->decoratedCte = $decoratedCte;
    }

    public function add(SqlComponent $component): void
    {
        $this->decoratedCte->add($component);
        $component->setParent($this);
    }

    public function initializeWithDependencies(
        TablesAliasHelper $helper,
        QueryState $initialState,
    ): void {
        $this->decoratedCte->initializeWithDependencies($helper, $initialState);
    }

    abstract public function build(): string;

    public function getState(): QueryState
    {
        return $this->decoratedCte->getState();
    }
}