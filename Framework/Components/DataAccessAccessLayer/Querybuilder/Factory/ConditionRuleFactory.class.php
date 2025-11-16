<?php

declare(strict_types=1);
class ConditionRuleFactory
{
    public function __construct(
        private EntityManagerInterface $em,
        private QueryState $state,
    ) {
    }

    public function create(string $method, mixed $data): ConditionRuleInterface
    {
        $ruleType = ConditionRuleType::getRuleType($method);

        $rule = match ($ruleType) {
            ConditionRuleType::WHERE,
            ConditionRuleType::IN,
            ConditionRuleType::ON => new WhereRules(
                $data,
                $this->em,
                $method,
                $this->state,
            ),
            ConditionRuleType::SET => new SetRules(
                $method,
                $data,
                $this->em,
                $this->tables,
            ),
            ConditionRuleType::INSERT => new InsertRules(
                $method,
                $data,
                $this->em,
                $this->tables,
            ),
            default => throw new BadQueryArgumentException(
                "Unsupported condition rule type for method: {$method}",
            )
        };

        // Initialize the rule immediately with the shared ParameterManager
        if (method_exists($rule, 'initialize')) {
            $rule->initialize($this->state);
        }

        return $rule;
    }
}