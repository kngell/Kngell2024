<?php

declare(strict_types=1);

class DataManipulationRuleFactory extends AbstractRulesFactory implements RuleFactoryInterface
{
    public function supports(SqlStatementType $statement): bool
    {
        return in_array($statement, [SqlStatementType::INSERT, SqlStatementType::UPDATE, SqlStatementType::DELETE, SqlStatementType::MERGE]);
    }

    public function create(string $method, mixed $data): QueryRulesInterface
    {
        $statement = SqlStatementType::tryFrom($method);
        return match(true) {
            $statement === SqlStatementType::INSERT => $this->initialize(new InsertRules(
                $this->em,
                $method,
                $this->state,
                $data,
            )),
            default => $this->initialize(new GenericRule(
                $this->em,
                $method,
                $this->state,
            ))
        };
    }
}