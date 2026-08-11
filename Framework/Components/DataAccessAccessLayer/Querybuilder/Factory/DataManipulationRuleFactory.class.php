<?php

declare(strict_types=1);

class DataManipulationRuleFactory extends AbstractRulesFactory
{
    private const SUPPORTED_TYPES = [
        SqlStatement::INSERT,
        SqlStatement::UPDATE,
        SqlStatement::DELETE,
        SqlStatement::MERGE,
    ];

    public function supports(SqlStatement $statement): bool
    {
        return in_array($statement, self::SUPPORTED_TYPES);
    }

    public function create(string $method, mixed $data): QueryRulesInterface
    {
        $statement = SqlClause::tryFrom(strtoupper($method))->toStatementType();
        $context = $this->component->getContext();
        return match(true) {
            $statement === SqlStatement::INSERT => $this->initialize(new InsertRules(
                $this->em,
                $method,
                $this->state,
                $data,
            )),
            $statement === SqlStatement::UPDATE && $context === StatementType::SIMPLE_UPDATE => $this->initialize(new SetRule(
                $this->em,
                $method,
                $this->state,
                $data,
            )),
            $statement === SqlStatement::UPDATE && $context === StatementType::BULK_UPDATE => $this->initialize(new BulkSetRule(
                $this->em,
                $method,
                $this->state,
                $data,
            )),
            $statement === SqlStatement::UPDATE && $context === StatementType::BULK_UPDATE_MARIADB => $this->initialize(new BulkSetRule(
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