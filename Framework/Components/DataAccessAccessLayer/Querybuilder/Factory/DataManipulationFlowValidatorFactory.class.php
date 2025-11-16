<?php

declare(strict_types=1);
class DataManipulationFlowValidatorFactory implements FlowValidatorFactoryInterface
{
    public function __construct(private SqlQueryComponent $component)
    {
    }

    public function supports(SqlStatementType $statement): bool
    {
        return in_array($statement, [SqlStatementType::INSERT, SqlStatementType::UPDATE, SqlStatementType::DELETE, SqlStatementType::MERGE]);
    }

    public function create(): FlowValidatorInterface
    {
        $statementType = $this->component->getSqlClause();
        return match (true) {
            $statementType === SqlStatementType::INSERT => new QueryFlowValidatorForInsert($this->component),
            $statementType === SqlStatementType::UPDATE => new QueryFlowValidatorForUpdate($this->component),
            default => new GenericQueryFlowValidator($this->component)
        };
    }
}