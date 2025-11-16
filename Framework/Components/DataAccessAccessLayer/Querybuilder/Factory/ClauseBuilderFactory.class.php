<?php

declare(strict_types=1);

class ClauseBuilderFactory
{
    /** @var ClauseBuilderFactoryInterface[] */
    private array $factories;

    public function __construct(
        private SqlQueryComponent $component,
    ) {
        $this->factories = $this->factories();
    }

    public function create(SqlStatementType $statementType): ?ClauseBuilderInterface
    {
        foreach ($this->factories as $factory) {
            if ($factory->supports($statementType)) {
                return $factory->create();
            }
        }
        return null;
    }

    /**
     * @return ClauseBuilderFactoryInterface[]
     */
    private function factories(): array
    {
        return [
            new DataQueryClauseBuilderFactory(
                $this->component,
            ),
            new DataManipulationClauseBuilderFactory(
                $this->component,
            ),
        ];
    }
}