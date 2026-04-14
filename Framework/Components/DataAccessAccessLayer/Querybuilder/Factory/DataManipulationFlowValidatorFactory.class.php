<?php

declare(strict_types=1);
class DataManipulationFlowValidatorFactory implements FlowValidatorFactoryInterface
{
    public function __construct(private SqlComponent $component)
    {
    }

    public function supports(SqlStatement $statement): bool
    {
        return in_array($statement, [SqlStatement::INSERT, SqlStatement::UPDATE, SqlStatement::DELETE, SqlStatement::MERGE]);
    }

    public function create(): FlowValidatorInterface
    {
        $statementType = $this->component->getStatement();
        return match (true) {
            $statementType === SqlStatement::INSERT => new QueryFlowValidatorForInsert($this->component),
            $statementType === SqlStatement::UPDATE => new QueryFlowValidatorForUpdate($this->component),
            $statementType === SqlStatement::DELETE => new QueryFlowValidatorForDelete($this->component),
            default => new GenericQueryFlowValidator($this->component)
        };
    }
}
