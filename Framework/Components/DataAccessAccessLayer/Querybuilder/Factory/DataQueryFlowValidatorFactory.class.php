<?php

declare(strict_types=1);
class DataQueryFlowValidatorFactory implements FlowValidatorFactoryInterface
{
    public function __construct(private SqlComponent $component)
    {
    }

    public function supports(SqlStatement $statement): bool
    {
        return $statement === SqlStatement::SELECT;
    }

    public function create(): FlowValidatorInterface
    {
        return new QueryFlowValidatorForSelect($this->component);
    }
}
