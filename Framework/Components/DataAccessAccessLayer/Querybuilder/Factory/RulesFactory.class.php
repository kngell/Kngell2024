<?php

declare(strict_types=1);

class RulesFactory
{
    /** @var RuleFactoryInterface[] */
    private array $factories;

    public function __construct(
        private EntityManagerInterface $em,
        private QueryState $state,
    ) {
        $this->factories = $this->factories();
    }

    public function create(string $method, array $data): ?QueryRulesInterface
    {
        $statementType = SqlBuilderMethodRegistry::getClauseContext($method)->toStatementType();
        if ($statementType === null) {
            return null;
        }
        foreach ($this->factories as $factory) {
            if ($factory->supports($statementType)) {
                return $factory->create($method, $data);
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
            ),
            new DataManipulationRuleFactory(
                $this->em,
                $this->state,
            ),
        ];
    }
}