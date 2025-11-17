<?php

declare(strict_types=1);

class FactoryRegistry
{
    private array $clauseBuilderFactories;
    private array $flowValidatorFactories;
    private array $ruleFactories;
    private array $standardizerFactories;

    public function __construct(
        private SqlQueryComponent $component,
        private EntityManagerInterface $em,
        private QueryState $state,
    ) {
        $this->initializeFactories();
    }

    public function getClauseBuilder(SqlStatementType $type): ?ClauseBuilderInterface
    {
        return $this->findFactory($this->clauseBuilderFactories, $type)?->create();
    }

    public function getFlowValidator(SqlStatementType $type): ?FlowValidatorInterface
    {
        return $this->findFactory($this->flowValidatorFactories, $type)?->create();
    }

    public function getRule(string $method, array $data): ?QueryRulesInterface
    {
        $statementType = SqlBuilderMethodRegistry::getClauseContext($method)->toStatementType();
        if (!$statementType) {
            return null;
        }

        $factory = $this->findFactory($this->ruleFactories, $statementType);
        return $factory?->create($method, $data);
    }

    public function getStandardizer(SqlStatementType $type): ?DataStandardizerInterface
    {
        return $this->findFactory($this->standardizerFactories, $type)?->create($type);
    }

    private function initializeFactories(): void
    {
        // Clause Builder Factories
        $this->clauseBuilderFactories = [
            new DataQueryClauseBuilderFactory($this->component),
            new DataManipulationClauseBuilderFactory($this->component),
        ];

        // Flow Validator Factories
        $this->flowValidatorFactories = [
            new DataQueryFlowValidatorFactory($this->component),
            new DataManipulationFlowValidatorFactory($this->component),
        ];

        // Rule Factories
        $this->ruleFactories = [
            new DataQueryRuleFactory($this->em, $this->state),
            new DataManipulationRuleFactory($this->em, $this->state),
        ];

        // Standardizer Factories
        $this->standardizerFactories = [
            new StandardizerFactory($this->component),
        ];
    }

    private function findFactory(array $factories, SqlStatementType $type): mixed
    {
        foreach ($factories as $factory) {
            if ($factory->supports($type)) {
                return $factory;
            }
        }
        return null;
    }
}