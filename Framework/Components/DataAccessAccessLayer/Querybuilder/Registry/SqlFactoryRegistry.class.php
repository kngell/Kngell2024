<?php

declare(strict_types=1);

class SqlFactoryRegistry
{
    private array $clauseBuilderFactories;
    private array $flowValidatorFactories;
    private array $ruleFactories;
    private array $standardizerFactories;
    private array $clauseStandardizerFactory;

    public function __construct(
        private SqlComponent $component,
        private EntityManagerInterface $em,
        private QueryState $state,
    ) {
        $this->initializeFactories();
    }

    public function getClauseBuilder(SqlStatement $type): ?ClauseBuilderInterface
    {
        return $this->findFactory($this->clauseBuilderFactories, $type)?->create();
    }

    public function getFlowValidator(SqlStatement $type): ?FlowValidatorInterface
    {
        return $this->findFactory($this->flowValidatorFactories, $type)?->create();
    }

    public function getRule(string $method, mixed $data): ?QueryRulesInterface
    {
        $statement = SqlBuilderMethodRegistry::getClauseContext($method)->toStatementType();

        if (!$statement) {
            return null;
        }

        $factory = $this->findFactory($this->ruleFactories, $statement);
        return $factory?->create($method, $data);
    }

    public function getStandardizer(SqlStatement $type): ?DataStandardizerInterface
    {
        return $this->findFactory($this->standardizerFactories, $type)?->create($type);
    }

    private function initializeFactories(): void
    {
        $this->clauseBuilderFactories = [
            new DataQueryClauseBuilderFactory($this->component),
            new DataManipulationClauseBuilderFactory($this->component),
        ];

        $this->flowValidatorFactories = [
            new DataQueryFlowValidatorFactory($this->component),
            new DataManipulationFlowValidatorFactory($this->component),
        ];

        // Rule Factories
        $bulkRowFactory = new BulkRowFactory();
        $this->ruleFactories = [
            new DataQueryRuleFactory($this->em, $this->state, $bulkRowFactory),
            new DataManipulationRuleFactory($this->em, $this->state, $bulkRowFactory),
        ];

        // Standardizer Factories
        $this->standardizerFactories = [
            new StandardizerFactory(),
        ];

        $this->clauseStandardizerFactory = [
            new ClauseStandardizerFactory(),
        ];
    }

    private function findFactory(array $factories, SqlStatement $type): mixed
    {
        foreach ($factories as $factory) {
            if ($factory->supports($type)) {
                return $factory;
            }
        }
        return null;
    }
}