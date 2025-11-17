<?php

declare(strict_types=1);

class DataManipulationRuleFactory extends AbstractRuleFactory
{
    private const SUPPORTED_TYPES = [
        SqlStatementType::INSERT,
        SqlStatementType::UPDATE,
        SqlStatementType::DELETE,
        SqlStatementType::MERGE,
    ];

    public function supports(SqlStatementType $statement): bool
    {
        return in_array($statement, self::SUPPORTED_TYPES);
    }

    public function create(string $method, mixed $data): QueryRulesInterface
    {
        $statement = SqlClause::tryFrom(strtoupper($method))->toStatementType();

        return match($statement) {
            SqlStatementType::INSERT => $this->initialize(new InsertRules(
                $this->em,
                $method,
                $this->state,
                $data,
            )),
            SqlStatementType::UPDATE => $this->initialize(new UpdateRules(
                $this->em,
                $method,
                $this->state,
                $data,
            )),
            SqlStatementType::DELETE => $this->initialize(new DeleteRules(
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