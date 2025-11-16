<?php

declare(strict_types=1);

class DataQueryRuleFactory extends AbstractRulesFactory implements RuleFactoryInterface
{
    public function supports(SqlStatementType $statement): bool
    {
        return $statement === SqlStatementType::SELECT;
    }

    public function create(string $method, mixed $data): QueryRulesInterface
    {
        return $this->initialize(new WhereRules(
            $data,
            $this->em,
            $method,
            $this->state,
        ));
    }
}