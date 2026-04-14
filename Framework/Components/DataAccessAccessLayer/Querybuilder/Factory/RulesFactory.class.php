<?php

declare(strict_types=1);

class RulesFactory
{
    /** @var RuleFactoryInterface[] */
    private array $factories;

    public function __construct(
        private EntityManagerInterface $em,
        private QueryState $state,
        private BulkRowFactory $bulkRowFactory,
    ) {
        $this->factories = $this->factories();
    }

    public function create(string $method, mixed $data, ?StatementType $statementType = null): ?QueryRulesInterface
    {
        $statement = SqlBuilderMethodRegistry::getClauseContext($method)->toStatementType();
        if ($statement === null) {
            return null;
        }
        foreach ($this->factories as $factory) {
            if ($factory->supports($statement)) {
                return $factory->create($method, $data, $statementType);
            }
        }
        return null;
    }

    /**
     * @return RuleFactoryInterface[]
     */
    private function factories(): array
    {
        return [
            new DataQueryRuleFactory(
                $this->em,
                $this->state,
                $this->bulkRowFactory,
            ),
            new DataManipulationRuleFactory(
                $this->em,
                $this->state,
                $this->bulkRowFactory,
            ),
        ];
    }
}
